# Módulo RecurringTransaction — transações recorrentes

`Modules/RecurringTransaction`. Modelos de transação que geram ocorrências automaticamente; podem estar ligados a uma dívida ou meta.

## Tabela `recurring_transactions` (soft deletes)

`account_id`, `category_id`, `user_id`, `title` (≤255), `type` (`expense|revenue`), `amount`, `description`, `frequency` (`daily|weekly|monthly|yearly|custom`), `custom_frequency_days`, `start_date`, `end_date`, `last_generated_at`, `is_active`, `is_linked`, `linked_type` (FQCN de `Debt` ou `FinancialGoal`), `linked_id`.

`transactions.is_recurring` / `transactions.recurring_transaction_id` ligam as ocorrências geradas.

Modelo: relações `user`, `account`, `category`, `transactions`, `linked` (morphTo); helpers `nextDateAfter()`, accessor `next_occurrence` (null depois de `end_date`), `isExpired()`, scope `active`.

## Gerador (`Services/RecurringTransactionGenerator.php`)

- Corre "como" o dono (`Auth::setUser`).
- Itera desde `start_date` (ou próxima data após `last_generated_at`) enquanto a data ≤ hoje e ≤ `end_date` — **faz backfill** de datas perdidas. Cada ocorrência é `completed` com `is_recurring` e `recurring_transaction_id`.
- **Sem ligação** → `TransactionRepository::store` (transação normal).
- **Ligada a dívida** → `DebtPaymentRepository::store` (`interest_rate` 0, `is_monthly_payment` false) → cria a despesa e atualiza `paid_amount`.
- **Ligada a meta** → `FinancialGoalTransactionRepository::store`; `expense` → `contribution`, `revenue` → `withdrawal` (reserva, não transação de conta).
- Desativa a recorrente quando expira. Também gera logo no `store`.
- Job `Jobs/GenerateRecurringTransactionsJob.php` agendado `daily()` em `RecurringTransactionServiceProvider::boot` (o `Routes/console.php` duplicado não é carregado).

## Permissões (`Policies/RecurringTransactionPolicy.php`)

- `show`: qualquer membro da conta.
- `update`/`delete`: creator da conta ou `editRecurringTransaction` / `destroyRecurringTransaction`.
- `store`: autoriza criação na conta; a dívida/meta ligada é verificada pelos repositories durante a geração.
- `update` só aceita `category_id`, `amount`, `description`, `end_date`, `custom_frequency_days`, `title`. `destroy` é soft delete.

## Validação

`StoreRecurringTransactionRequest`/`UpdateRecurringTransactionRequest` herdam `RecurringTransactionRequest`: lança HttpResponseException com JSON 422 `{success:false, message, errors}` (mesmo sem header Accept); o handler global atual acrescenta `data:null` na API. Mensagens em `recurringtransaction::validation` (pt/en). Conta obrigatória; campos de ligação podem vir null quando não ligada. Categoria validada por `SelectableCategory` (referências arquivadas mantêm-se).

## Resumo (`Repositories/RecurringSummaryRepository.php`)

Partilhado com o dashboard. Âmbito: todas as contas onde o utilizador é membro; ignora filtros/paginação; exclui soft-deleted.
- Normalização mensal: daily ×30, weekly ×52/12, yearly ÷12, custom ×30/dias.
- Totais **por moeda**; ligadas a metas vão para `monthlyReservations`/`monthlyReleases`.
- Upcoming: próximos 7 dias, máximo 5.

## Rotas (`/api/v1`, `auth:sanctum` + `setlocale`)

| Método | Caminho | Notas |
|---|---|---|
| GET | `recurrings` | ModulePaginate; `?includeSummary=1` acrescenta `meta.summary`. Filtros: `type`, `categoryId`, `isActive`, `frequency`, `accountId`, `userId`, `startDate`, `endDate` |
| POST | `recurrings` | cria e gera ocorrências devidas |
| GET/PUT/DELETE | `recurrings/{id}` | show usa a policy |
| POST | `recurrings/{id}/toggle` | ativa/desativa |

Testes: `Modules/RecurringTransaction/Tests/Feature/RecurringListSummaryTest.php`, `RecurringTransactionValidationTest.php`, `Modules/RecurringTransaction/Tests/Unit/RecurringSummaryTest.php`, `RecurringPresentationTest.php`.
