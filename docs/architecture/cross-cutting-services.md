# Arquitetura — serviços transversais

Activity log, notificações, emails, i18n, moedas, agendamentos, filas e cache.

## Activity Log (`Modules/ActivityLog`)

- Tabela `activity_logs(type enum account|debt|financial_goal, user_id, subject_id, metadata json)`.
- **Escrita direta, não por eventos:**
  ```php
  app(ActivityLogRepository::class)->storeActivity($subjectId, Auth::id(), 'account', ['type' => 'transaction_added', ...]);
  ```
- **Quem escreve:**
  - repositories de Account, Transaction, AccountUser(Invite);
  - repositories de Debt, DebtPayment, DebtUser(Invite);
  - repositories de FinancialGoal, FinancialGoalUser(Invite) e `GoalReservations::store`.
- `destroyByType` apaga o histórico quando o recurso é apagado.
- **Leitura:** `GET accounts|debts|financial-goals/{id}/activity` usa `ActivityLogPaginate` (propriedades `type` e `id`).
- `Core/ActivityLogMessageResolver.php` traduz `metadata.type` em `activitylog::messages.{tipo}.messages.{evento}` e formata `changes` (old/new, ids resolvidos para nomes de moeda/categoria) e montantes.
- **Eventos usados:**
  - `account_*`, `transaction_*` (`added`, `scheduled`, `updated`, `deleted`, `confirmed`);
  - `debt_*`, `payment_*`;
  - `goal_*`, `financial_transaction_*`;
  - eventos de membros (ver [auth-and-permissions.md](auth-and-permissions.md)).

## Notificações in-app (`Modules/Notification`)

- **Enviar a partir de outro módulo:**
  ```php
  app(SendNotificationAction::class)->execute(
      new CreateNotificationDto(['username' => $sender->username], 'FRIEND_REQUEST_RECEIVED'),
      $receiver
  );
  ```
  1. Cria a linha em `notifications`.
  2. Dispara `NotificationCreated`.
  3. `NotificationCreatedListener` invalida a cache e envia email **se** a preferência do utilizador para esse `type_code` tiver `email=true`.
- **Templates:**
  - Vivem em `notification_types`: JSON por idioma com title, message, mail_subject, mail_message, mail_signature e pathname.
  - As keywords `[$keyword]` (tabela `notification_keywords`, ligada ao tipo por pivot) são substituídas por `data[keyword]` (`RenderNotificationTemplateAction`, no idioma do utilizador).
- **Preferências:** `user_notification_preferences(user_id, type_code, in_app, email)`.
- **Broadcasts (admin):**
  - `BroadcastNotificationService` cria um tipo `is_broadcast` e uma linha em `broadcast_notifications`.
  - Se `send_email` estiver ativo, `BroadcastNotificationObserver` envia aos utilizadores não-admin em chunks de 50, através de `SendNotificationEmailJob` na fila `emails`, com 10 s de atraso a mais por chunk.
- **Feed:** `NotificationCacheService` (cache de 5 min, chaves `notifications:feed:{uid}` e `notifications:unread:{uid}`) junta as 20 notificações mais recentes não arquivadas com os 20 broadcasts mais recentes.
- **Quem envia hoje:** só o módulo Friends (`FRIEND_REQUEST_RECEIVED`, `FRIEND_REQUEST_ACCEPTED`, `FRIEND_REQUEST_DECLINED`).
- **Tipos:** não há seeder; criam-se no backoffice.
- **Budgets (planeado):** as notificações de orçamento têm de ser só in-app, mesmo que o utilizador tenha email ativo. O listener atual enviaria email.

## Emails transacionais (`Modules/User` + `Modules/EmailLayout`)

| Evento | Disparado por | email_type code |
|---|---|---|
| `VerifyEmail` | `UserObserver::created` | `verify_email` |
| `EmailVerified` | `AuthController@verifyEmail` | `email_verified` |
| `ResetPassword` | `AuthController@resetPassword` | `reset_password` |
| `PasswordChanged` | `UserRepository@updatePassword` | `password_changed` |

- **Cadeia de envio (síncrona):** listener → Notification → Mailable.
- O Mailable carrega o layout com `EmailLayoutRepository::showByEmailTypeCode(code)` e substitui `[$user_name]`, `[$verification_link]`, `[$reset_password_link]` e `[$logo]`.
- **Os `email_types` e `email_layouts` têm de existir na BD**, porque não há seeder; sem eles o envio dá erro null.

## Internacionalização

- **Idiomas:** `pt` e `en` (tabela `languages`).
- **Middleware `setlocale`** (`Modules/UserPreferences/Http/Middleware/SetLocale.php`):
  - lê `user->preferences->lang`;
  - define o locale da App e do Carbon;
  - grava o cookie `lang`;
  - **exige utilizador autenticado**.
- **Traduções:** em `Modules/*/Resources/lang/{pt,en}`, usadas como `__('accounts::messages.accounts.store', [...])`.
  - `tests/Unit/TranslationCatalogueTest.php` garante que as chaves e placeholders coincidem entre idiomas e que as referências existem.
  - Namespaces canónicos: `friends::messages.friendship-requests` e `debts::attributes.debt-payments`.
- **Dados multilíngues:** os nomes de categorias e moedas são JSON (`CategoryNameCast`, `CurrencyNameCast`), e o mesmo acontece nos títulos e mensagens das notificações.

## Moedas e conversão

- `currencies.rate` guarda unidades por 1 USD.
- **Conversão:** `amount * to.rate / from.rate` (`Modules/Currency/Core/Helpers::convertCurrency`). Em muitas queries está escrita inline em SQL: `user_currency.rate / account_currency.rate`.
- **Convertem** para a moeda preferida do utilizador:
  - totais do dashboard;
  - stats de contas (`AccountRepository::getStats`);
  - `User::getBalance()`.
- **Não convertem** (totais separados por moeda):
  - Account Resume;
  - resumo de recorrentes;
  - metas (só aceitam contas na moeda da meta).

## Agendamentos, filas e cache

| Tarefa | Registo | Frequência |
|---|---|---|
| `currency:fetch-daily` | `CurrencyServiceProvider` | diária às 01:00 `Europe/Lisbon`, `withoutOverlapping`, `onOneServer`, log em `storage/logs/currency-rates.log` |
| `GenerateRecurringTransactionsJob` | `RecurringTransactionServiceProvider::boot` | `daily()`, `withoutOverlapping`, `onOneServer` |
| `SendNotificationEmailJob` | fila `emails` | quando se cria um broadcast com email |

- Tudo depende de um único cron minutal `php artisan schedule:run`.
- **Cache:** determinada por `CACHE_STORE`; serve para permissões, feed e locks. `onOneServer` requer armazenamento adequado e partilhado entre instâncias.
- **Fila:** determinada por `QUEUE_CONNECTION`. Com `sync`, os jobs correm inline; com driver assíncrono, é necessário worker para `default` e `emails`. Não assumir drivers ativos de produção a partir de cópias de ambiente.
- Recorrentes usam `daily()` sem timezone explícito: seguem UTC no `config/app.php` atual. O scheduler apenas despacha o job quando a fila é assíncrona.
- Roteiro completo: [operação e deploy](../operations/deployment.md).
