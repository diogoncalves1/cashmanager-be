# Problemas conhecidos e dívida técnica

A secção seguinte foi revista em **20/11/2026** contra o código local. O restante levantamento é anterior: os seus ✅ indicam confirmação na análise original, não nova reprodução nesta revisão. As alterações locais podem ter mudado esses itens. Quando um item for resolvido, remover ou atualizar a entrada e o contrato do domínio.

## Refatoração de listagens concluída nesta migração

A API usa [ModulePaginate/Filter/Search](module-listings.md), com validação 422, pesquisa agrupada e filtros com valores zero/intervalos. Foram corrigidos o desfasamento de moeda/flags de metas nas transações, o subject dos convites de contas, as permissões de pagamentos e a união paginada de notificações. Os testes estão nos módulos correspondentes.

Permanecem os âmbitos históricos dos agregados globais de contas/dívidas/metas e questões de policies/mutações fora desta migração. Não interpretar um agregado global como soma da página. A app Flutter/outros clientes ainda precisam de confirmação de adaptação ao contrato antes do deploy.

## Bugs prováveis

| # | Onde | Problema |
|---|---|---|
| 1 ✅ | `Modules/Debts/Repositories/DebtRepository.php:216` (`reset`) | Verifica a permissão `updateFinancialGoal` em vez de uma permissão de dívida (`editDebt`). |
| 2 ✅ | `Modules/Friends/Repositories/FriendshipRequestRepository.php:212` | O limite de recusas filtra `status('decline')`, mas o enum é `declined`, pelo que o limite nunca dispara. |
| 3 ✅ | `Modules/ActivityLog/Repositories/ActivityLogRepository.php:33` | `testType` lança exceção quando `tryFrom($type)` **tem** sucesso (lógica invertida). |
| 4 ✅ | `Modules/Asset/.../create_asset_seach_view_table.php` `down()` | Faz drop de `accounts_view` em vez de `asset_search_view`. |
| 5 ✅ | `Modules/Asset/Routes/api.php` | `GET v1/asset` aponta para `index`, que não existe em `Api/V1/AssetController`. |
| 6 ✅ | Permissão `viewAdmin` | Usada pelo middleware `admin`, mas não é semeada em nenhuma migration. |
| 7 | `DebtPaymentRepository::updatePaidAmount` / `reversePaidAmount` | Recalcula `paid_amount` sobre todos os pagamentos sem filtrar `completed`. |
| 8 | `Notification` listeners | Chamam `Notification::send($notificationEntity, …)` com uma entidade que não é `Notifiable`. `NotificationCreatedMail` usa os campos JSON por idioma sem os resolver. |
| 9 | `UserNotificationPreferences` | O `fillable` tem `type_id`, mas a coluna é `type_code`. |
| 10 | `SetLocale` | O mapa de locales do Carbon só cobre pt, en e es; outro código de idioma rebenta. Também rebenta sem utilizador autenticado, e nas rotas públicas de Currency está antes de `auth:sanctum`. |
| 12 | `Modules/Friends/Traits/Friendable.php` | Chama métodos que não existem. Não é usado. |

## Segurança

- ✅ **IDOR nas notificações:** `POST notifications/{id}/read`, `POST notifications/{id}/archive` e `DELETE notifications/{id}` usam `Notification::findOrFail($id)` sem verificar o dono. Qualquer utilizador autenticado pode marcar como lida, arquivar ou apagar notificações de outro.
- **Tokens enviados por email:** os tokens de verificação e de reset de password são tokens Sanctum completos. Dão acesso a toda a API, não expiram e não são revogados depois de usados.
- **Enumeração de emails:** `POST reset-password` devolve 500 para emails que não existem e 200 para os que existem.
- **Dados pessoais expostos:** as respostas de `friendship-requests/{id}/send` e de invite/accept/user-role das dívidas incluem o email de outros utilizadores.

- **Comando com input não escapado:** `AssetRepository::store` faz `exec("python3 … $ticker")` sem `escapeshellarg`. Dentro do backoffice é admin-only, mas deve ser corrigido.
- **Rotas de Asset sem autenticação:** os endpoints `GET v1/assets/*` são públicos.
- **Escrita de Currency fora do Sanctum:** é protegida só por sessão web (`auth`, `web`), sem verificar permissão global na rota. `GET currencies/update-rates` é um GET com efeitos.
- **Backoffice de EmailLayout:** o middleware `admin` está comentado; basta `auth`.

## Inconsistências

- **Valores por defeito das preferências:** o `UserObserver` cria `lang='en'`, mas o default da migration é `pt`. A moeda está hard-coded como `currency_id=1`.
- **Códigos HTTP errados:** várias exceções "Unauthorized…" e de validação usam o código 500 (por exemplo `UnauthorizedView*`, `InvalidTransactionPendingDateException`, `TransactionAlreadyConfirmedException` e as exceções de SharedRoles).
- **Visibilidade:** listas financeiras agora exigem pertença/permissão na mesma membership; resumos globais anteriores e endpoints fora das listas mantêm as suas regras.
- **`accounts_view`:** não exclui as transações neutralizadas das metas (o global scope do modelo exclui). Também esconde contas sem moeda.
- **`transactions_view`:** usa INNER JOIN em categorias, por isso esconde transações sem categoria.
- **`DashboardCharts`:** a rota não tem `setlocale`, por isso as mensagens ficam no locale por defeito.
- **Código morto em FinancialGoalRepository:** `adjustContributedAmount`, `updateContributedAmount` e `reverseContributedAmount` são restos do modelo antigo.
- **`Modules/RecurringTransaction/Routes/console.php`:** duplica o agendamento, mas não é carregado.
- **Hora do agendamento de câmbios:** o `CurrencyServiceProvider` agenda para as **01:00**, mas uma versão anterior do AGENTS.md dizia 10:30. O código é que vale.
- **Comentários "Finished: True":** aparecem no topo de vários controllers. São apenas marcas do autor.

## Encontrados ao documentar a API (por verificar)

Vieram da leitura de controllers, requests, repositories e resources durante a escrita de `docs/api/`. Cada ficheiro da API assinala o comportamento atual junto do endpoint respetivo.

### Códigos HTTP e erros
- **Recursos inexistentes devolvem 500 em vez de 404.** Acontece em account update/destroy/status, recurrings, currencies, invites e membros das metas, e notificações. A causa é `firstOrFail`/`findOrFail` apanhado por `catch (\Exception)` ou por `safe()`.
- **Erros de negócio que devolvem 500:**
  - os locks 422 de contas (`currency_locked`, `account_history`), apanhados pelo catch genérico;
  - 5 exceções de transações;
  - exceções de SharedRoles (que usam 401 ou 500);
  - Friends;
  - `revoke-user` sobre si próprio, que lança `new \Exception()` vazia.
- **Membros sem shared role:** vários caminhos chamam métodos sobre um shared role `null` e dão um erro PHP com 500. Acontece em debt show/update/activity, payment store, `leave`, `user-role` e `POST recurrings` (`AccountPolicy::isOwner(null)`).
- **`register`:** a validação devolve 500. Além disso, `UserRepository::store` engole exceções e responde 200 mesmo quando o utilizador não é criado.

### Validação
- **`POST transactions`:** `status` é `nullable` nas regras, mas o repository lê `$input['status']`. Na prática é obrigatório.
- **`PUT transactions` / `confirm`:** a regra de datas não corre, e `confirm` aceita uma transação pending com data futura.
- **`DebtRequest`:** `installments => nullable|integrer` (gralha → 500). `months` e `monthly_amount` são gravados sem validação.
- **`POST debt-payments`:** exige `interest_rate` e `is_monthly_payment` no body sem os validar (undefined index → 500).
- **`PUT me`:** não tem validação. Sem `lang` dá 500, e um email ou username duplicado também dá 500.
- **`check-username`:** sem o parâmetro devolve `exists: true`.
- **Filtros de recorrentes:** um filtro sem operador ou com operador não permitido dá 500.

### Cálculos e dados
- **`Helpers::percentage(total, x)`:** devolve `(x − total)/total × 100`. Por isso `percentageCompleted` sai negativo e `difLastMonth` sai errado.
- **Listagem de dívidas:** um `months` null passado a um parâmetro `int` faz falhar a listagem inteira.
- **`DebtPaymentRepository::confirm`:** não devolve o resultado, por isso a resposta vem com `data: []`.
- **Store de dívidas e metas:** devolve `status: null` porque o modelo não é recarregado.
- **Dashboard:**
  - conta só as transações criadas pelo utilizador, e não as das contas de que é membro;
  - as percentagens comparam totais acumulados;
  - os valores vêm como strings formatadas.
- **Account show:**
  - `balanceChart` pode sair como objeto em vez de array;
  - `balanceLastMonth` é quase sempre 0.
- **Friends:**
  - `stats.friends` também conta os bloqueios;
  - a lista de bloqueados inclui quem bloqueou o utilizador;
  - `decline` e `cancel` falham quando o pedido está no sentido contrário;
- **Notificações:**
  - `meta.count` inclui as arquivadas.
- As três listagens de convites reutilizam ainda as traduções de estado das metas; o subject de contas usa AccountResource.
- **Tipos de notificação em falta:** `FRIEND_REQUEST_*` não são semeados. Se não existirem, enviar, aceitar ou recusar pedidos de amizade dá 500.

### Nomes de campos (manter enquanto o frontend depender deles)
- Gralhas: `typeTransalated`, `insterestRate`, `totalAmounDebt`, `percentageCompeted`, `*Formated`.
- `DebtResource` devolve `isRecurring` e `type`, que estão sempre a null.
- A API paginada não usa draw/recordsTotal/recordsFiltered; o backoffice mantém o seu protocolo.
