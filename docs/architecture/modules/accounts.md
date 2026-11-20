# Módulo Accounts — contas, transações, membros

`Modules/Accounts`. Contas partilhadas, transações, membros/convites, analytics por conta e o "Account Resume" global.

## Tabelas e views

- `accounts`: `name`, `type` (`cash|bank_account|credit_card|digital_wallet`), `balance` decimal(15,2) default 0, `currency_id`, `active`.
- `transactions`: `user_id`, `account_id`, `type` (`expense|revenue`), `amount`, `date`, `description`, `status` (`pending|completed`), `category_id`, `is_recurring`, `recurring_transaction_id`.
- `account_users` (pivot com `shared_role_id`), `account_user_invites`.
- **Views**:
  - `accounts_view`: conta + moeda, `totalTransactions`, `totalRevenues`/`totalExpenses` (só completed), `userNames`/`user_ids` (GROUP_CONCAT).
  - `transactions_view`: transação "achatada" com categoria, user, conta e moeda (INNER JOIN em categorias → sem categoria não aparece).
  - `account_basic_view`: id, name, type, `user_ids`, active (para pickers).

## Modelos (`Entities/`)

- `Account` (IsShareable): `transactions`, `transactionsView`, `currency`, `users` (pivot `AccountUser` com `shared_role_id`), `invites`, `creator()`.
- `Transaction`: global scope `goal_reservations` que exclui ids de `financial_goal_neutralized_transactions` (legado da migração das metas). Relações `user`, `account`, `category`, `sharedRole`.
- `AccountUser`, `AccountUserInvite` (pivots), `AccountsView`, `TransactionsView`, `AccountBasicView` (read-only).

## Regras de negócio

### Saldo
- `accounts.balance` é **guardado e ajustado incrementalmente**, nunca recalculado. Toda a conta começa a 0 (`AccountRequest` não aceita `balance`).
- **Só transações `completed` mexem no saldo.** Pending nunca.
- Não existe conceito de transferência: só `expense` e `revenue`.

### Transações (`Repositories/TransactionRepository.php`)
| Operação | Permissão | Regras |
|---|---|---|
| store | `createTransaction` | lock user+account. Data futura+completed ou data passada+pending → `InvalidTransactionPendingDateException`. Só para `TransactionRequest` corre `SelectableCategory::lockAndValidate`. Se completed: `adjustBalance` (+revenue / −expense) |
| update | `editTransaction` | só `amount`, `date`, `description`, `category_id` mudam (type/status/account imutáveis). Se completed aplica diferença. Bloqueado se a transação está ligada a uma meta |
| destroy | `destroyTransaction` | se completed reverte saldo. Mesmo bloqueio de metas |
| confirm | `confirmTransaction` | pending → completed, `adjustBalance`. Já confirmada → `TransactionAlreadyConfirmedException` |

`confirm`/`destroy` aceitam `$checkPermission=false` para chamadas internas (Debts, Recorrentes). Cada mutação escreve Activity Log.

### Contas (`Repositories/AccountRepository.php`)
- `store`: cria conta + `AccountUser` com role `creator`.
- `update`/`status` (toggle `active`): Gate `update` → policy aceita creator ou `editAccount`; Request aceita atualização parcial em PUT/PATCH. Mudar moeda é bloqueado se houver movimentos de metas na conta.
- `destroy`: chama Gate `destroy`, mas a policy define `delete`, pelo que a autorização está desalinhada. Após autorização, o fluxo bloqueia histórico de metas e elimina a conta/activity log.
- `showToUser`: Gate `show`, cuja policy aceita membership sem verificar `viewAccount`. Não confundir com Account Resume, que exige essa permissão.
- `getStats` (património, receitas, despesas): só contas onde o role tem `updateUserBalance`, convertidas para a moeda do utilizador.
- `getAccountsByUserPermission` (picker): contas ativas do utilizador com relação de permissão; a relação de `accountUsers` não está limitada ao próprio membro, ver problemas conhecidos.

### Account Resume (`Repositories/AccountResumeRepository.php`)
`GET /api/v1/account/resume?period=3m|12m|ytd|all&currency=XXX`
- Contas com `viewAccount` filtradas por **uma** moeda (preferida ou a primeira disponível); moeda desconhecida → 422. Nunca converte.
- Exclui transações futuras e pending dos totais completed.
- Devolve: summary (balance, income, expenses, net, savingsRate, averageDailyExpense, contagens), série mensal com zeros, por conta (`reservedAmount`/`availableAmount` via `GoalReservations`), categorias de despesa, 8 últimos movimentos.
- Teste: `Modules/Accounts/Tests/Feature/AccountResumeTest.php`.

### Detalhe da conta (`AccountController@show`)
Devolve a conta + `meta`: `monthlyResume`, `categorySummary`, `extraData` (mês atual vs anterior) e `balanceChart` (saldo diário acumulado em 7/30/90/365 dias).

## Rotas (`/api/v1`, `auth:sanctum` + `setlocale`)

| Método | Caminho | Handler |
|---|---|---|
| GET | `account/resume` | `AccountResumeController` (invokable) |
| GET | `accounts` | `AccountController@index` (paginação + `meta.stats`) |
| POST | `accounts` | `@store` |
| GET | `accounts/{id}` | `@show` |
| PUT/PATCH | `accounts/{id}` | `@update` |
| DELETE | `accounts/{id}` | `@destroy` |
| GET | `accounts/all` | `@allUser` |
| PATCH | `accounts/{id}/status` | `@status` |
| GET | `accounts/{id}/activity` | `@activity` |
| GET | `accounts/invitations-stats`, `sent-invitations`, `received-invitations` | `AccountUserInviteController` |
| POST/DELETE | `accounts/{id}/invite/{userId}` | `AccountUserInviteController@invite` / `@destroy` |
| POST | `accounts/{id}/accept`, `accounts/{id}/revoke` | `AccountUserInviteController` |
| POST | `accounts/{id}/revoke-user/{userId}` | `AccountUserController@revokeUser` |
| PUT | `accounts/{id}/user-role/{userId}` | `AccountUserController@updateUserRole` |
| DELETE | `accounts/{id}/leave` | `AccountUserController@leave` |
| apiResource | `transactions` | `TransactionController` (index paginado + `meta.stats`/`meta.categories`) |
| POST | `transactions/{id}/confirm` | `TransactionController@confirm` |

## Listagens e visibilidade atuais

- AccountPaginate/TransactionPaginate usam a [arquitetura comum de listagens](../module-listings.md), com EXISTS de pertença/permissão, Filter, Search e Resources por entidade. A transação preserva o global scope de exclusão dos movimentos neutralizados das metas. Totais/categorias reutilizam a consulta apropriada sem paginação.
