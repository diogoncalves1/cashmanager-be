# Módulo Debts — dívidas e pagamentos

`Modules/Debts`. Dívidas partilhadas cujos pagamentos criam despesas reais numa conta.

## Tabelas e views

- `debts`: `currency_id`, `name`, `total_amount`, `paid_amount`, `status` (`pending|paid`), `months`, `interest_rate`, `start_date`, `due_date`, `paid_at`, `months_paid`, `monthly_amount`, `description`.
- `debt_payments`: `transaction_id` (FK transactions, cascade), `debt_id`, `user_id`, `date`, `status` (`pending|completed`), `amount`, `interest_rate`, `interest_paid`, `is_monthly_payment`.
- `debts_user` (membros + `shared_role_id`), `debt_user_invites`.
- Categoria por defeito `debtPayment` (despesa) é semeada na migration.
- **Views**:
  - `debts_view`: dívida + moeda, `totalTransactions`, `totalPayments` (completed), `interestPaid`, `userNames`/`userIds`.
  - `debt_payments_view`: pagamento + dívida, user, conta (via `transactions.account_id`) e moeda.
  - `debt_basic_view`: `user_ids`, `status`, `target`, `current_amount`, moeda, taxa, mensalidade.
  - `user_debt_payment_view`: por (user, debt) `totalPaid` = Σ completed `amount − amount*interest_rate/100`.

## Modelos

`Debt` (IsShareable: `currency`, `users`, `payments`), `DebtPayment` (`transaction`, `debt`, `user`), `DebtUser`, `DebtUserInvite`, views `DebtsView`, `DebtBasicView`, `DebtPaymentView`, `UserDebtPaymentView`.

## Ciclo de vida da dívida (`Repositories/DebtRepository.php`)

```
pending ──markPaid (editDebt; paid_amount >= total)──► paid
paid    ──reset──────────────────────────────────────► pending
```
- `store`: cria dívida + `DebtUser` creator; log `debt_created`.
- `update` (`editDebt`): log `debt_updated` com diff por campo.
- `destroy` (`destroyDebt`).
- `markPaid`: `DebtNotPendingException`, `DebtNotFullyPaidException`; define `paid_at`.
- `reset`: `DebtPendingException` se já pending. ⚠️ verifica a permissão errada (ver problemas conhecidos).

## Pagamentos (`Repositories/DebtPaymentRepository.php`)

- **store** (`storeDebtPayment`; dívida tem de estar pending → `DebtNotInProgressException`):
  - Mesma regra de datas das transações (`PaymentBeforeCurrentDateException`).
  - Cria **sempre** uma despesa na conta, categoria `debtPayment`, via `TransactionRepository::store`, e guarda `transaction_id`.
  - Se completed: juros = restante × taxa%; principal = amount − juros (≥ 0); `paid_amount += principal`; `months_paid++` se `is_monthly_payment`.
- **update** (`editDebtPayment`): atualiza a transação; se completed recalcula `paid_amount` percorrendo todos os pagamentos por data.
- **destroy** (`destroyDebtPayment`): apaga transação e pagamento; se completed recalcula e decrementa `months_paid`.
- **confirm** (`confirmDebtPayment`): pending → completed; não pode ser data futura; `TransactionRepository::confirm` + ajuste de valores.
- Logs: `payment_added|scheduled|updated|deleted|confirmed`.

## Rotas (`/api/v1`, `auth:sanctum` + `setlocale`)

| Método | Caminho | Handler |
|---|---|---|
| apiResource | `debts` | `DebtController` (index = ModulePaginate + meta.stats) |
| GET | `debts/all` | `@allUser` (pending com `storeDebtPayment`) |
| GET | `debts/stats` | `@getStats` |
| GET | `debts/{id}/activity` | `@activity` |
| POST | `debts/{id}/mark-paid`, `debts/{id}/reset` | `DebtController` |
| GET | `debts/invitations-stats`, `sent-invitations`, `received-invitations` | `DebtUserInviteController` |
| POST/DELETE | `debts/{id}/invite/{userId}` | `DebtUserInviteController` |
| POST | `debts/{id}/accept`, `debts/{id}/revoke` | `DebtUserInviteController` |
| POST | `debts/{id}/revoke-user/{userId}` | `DebtUserController` |
| PUT | `debts/{id}/user-role/{userId}` | `DebtUserController` |
| DELETE | `debts/{id}/leave` | `DebtUserController` |
| apiResource | `debt-payments` | `DebtPaymentController` (index = ModulePaginate) |
| POST | `debt-payments/{id}/confirm` | `DebtPaymentController@confirm` |

Não emite notificações nem eventos.
