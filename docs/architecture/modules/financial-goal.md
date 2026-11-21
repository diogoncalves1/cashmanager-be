# Módulo FinancialGoal — metas de poupança (ledger de reservas)

`Modules/FinancialGoal`. Metas partilhadas suportadas por um **único ledger de reservas** (CM-120 / CM-250).
Documentos detalhados no módulo: `RESERVATIONS.md` (modelo) e `PRODUCTION-ROLLOUT.md` (migração em produção).

## Conceito

- **contribution**: reserva dinheiro de uma conta para a meta. **Não altera o saldo** da conta nem cria transação.
- **withdrawal**: liberta reserva (não pode exceder o reservado nessa conta para a meta).
- **usage**: gasta dinheiro reservado contra uma despesa real — liga uma despesa existente (`transaction_id`) ou cria uma (`expense_created = true`). Tem de ser completed.
- `contributed_amount` (cache) = contribuições completed − withdrawals completed (`sync()`).
- Disponível numa conta = `balance − reservas` (exposto como `reservedAmount`/`availableAmount`).

## Tabelas e views

- `financial_goals`: `currency_id`, `name`, `total_amount`, `contributed_amount`, `start_date`, `due_date`, `status` (`in_progress|completed|canceled`), `priority`, `completed_at`, `canceled_at`, `completed_funding`. (`funding_mode` foi removido.)
- `financial_goal_transactions`: `financial_goal_id`, `user_id`, `account_id`, `transaction_id`, `type` (`contribution|withdrawal|usage`), `amount`, `date`, `status` (`pending|completed`), `description`, `request_key` (único), `request_hash`, `expense_created`, `recurring_transaction_id` (único com `date`).
- `financial_goal_users`, `financial_goal_user_invites`.
- Tabelas do rollout: `financial_goal_request_receipts` (idempotência), `financial_goal_reservation_rollouts`, `financial_goal_reservation_rollout_events`, `financial_goal_neutralized_transactions` (transações históricas neutralizadas; escondidas pelo global scope de `Transaction`).
- Categorias `financialGoalContribution`/`financialGoalWithdrawal`: internas, mantidas só para histórico (`Category::INTERNAL_CODES`, não selecionáveis).
- **Views**: `financial_goal_view` (totais de contribuições/withdrawals completed, `userNames`/`userIds`), `user_financial_goal_contributions_view`, `financial_goal_basic_view` (`target`, `current_amount`), `financial_goal_transaction_view` (conta = `COALESCE(fgt.account_id, t.account_id)`, `balanceBefore` acumulado).

## Ciclo de vida da meta (`Repositories/FinancialGoalRepository.php`)

```
in_progress ──complete──► completed      (contributed >= total, sem pendentes; guarda completed_funding)
in_progress ──cancel────► canceled       (sem pendentes e reservedAmount = 0 → senão 422 release_first)
completed|canceled ──reset──► in_progress
```
- `update` (`updateFinancialGoal`): 422 `currency_locked` se houver movimentos; `target_locked` se completed.
- `destroy` (`destroyFinancialGoal`): 422 `history` se houver movimentos.
- Transições registam `goal_status_updated`. Completar **não** cria despesa.

## Ledger (`Services/GoalReservations.php`; o repository delega)

- **store**: lock user → account → goal. Permissões: meta `storeFinancialGoalTransaction` + conta `createTransaction` (403). Validações 422 em `financialgoal::reservations.*`: mesma moeda, conta ativa (exceto withdrawal), meta aberta, regra de datas (completed não futuro; pending não passado), withdrawal/usage ≤ reservado.
- **Idempotência**: `request_key` + hash SHA-256 em `financial_goal_request_receipts`; recorrentes chaveados por (`recurring_transaction_id`, `date`).
- **change**: `update` só em pending (completed → `immutable`); `confirm` idempotente; `destroy` em pending, ou em usage completed (desliga, mantém a despesa).
- Despesas ligadas a metas não podem ser editadas/apagadas pelo módulo Accounts.

## Rollout (migração do modelo antigo)

- Comando `goals:reservation-rollout [--apply] [--goal=] [--fail-on-review]` — preview sem escrita por omissão.
- `Services/MigrateGoalReservations.php`: tudo numa transação; aborta se algum plano estiver `blocked`; neutraliza cada transação histórica uma vez e ajusta `accounts.balance`.
- Migrations `2026_11_02_210000` (schema), `220000` (conversão), `230000` (unifica e remove `funding_mode`). `down()` lança exceção. Ordem e ensaio em `PRODUCTION-ROLLOUT.md`.
- Regressão de referência: meta 7 → 131,40 contribuições − 10 withdrawals = 121,40 reservado.

## Rotas (`/api/v1`, `auth:sanctum` + `setlocale`)

| Método | Caminho | Handler |
|---|---|---|
| apiResource | `financial-goals` | `FinancialGoalController` (show inclui 3 últimos movimentos + resumo mensal) |
| GET | `financial-goals/all`, `financial-goals/stats` | `FinancialGoalController` |
| GET | `financial-goals/{id}/activity` | `@activity` |
| POST | `financial-goals/{id}/cancel`, `complete`, `reset` | `FinancialGoalController` |
| GET | `financial-goals/{id}/reservations` | `GoalReservationsController@context` (reservado/usado/financiado + contas elegíveis da mesma moeda) |
| GET | `financial-goals/{id}/eligible-expenses?account_id&page&search` | `GoalReservationsController@expenses` (despesas completed não ligadas, 20/página) |
| convites/membros | igual a Debts (`invite`, `accept`, `revoke`, `revoke-user`, `user-role`, `leave`, `*-invitations`) | `FinancialGoalUserInviteController`, `FinancialGoalUserController` |
| apiResource | `financial-goal-transactions` | `FinancialGoalTransactionController` |
| POST | `financial-goal-transactions/{id}/confirm` | `@confirm` |

Notas: o paginator de movimentos usa `whereExists` sobre `financial_goal_users` (não join à view agregada) para ser compatível com `ONLY_FULL_GROUP_BY`. Testes: `Modules/FinancialGoal/Tests/Feature/GoalReservationsTest.php`, `PersonalCategoriesTest` (movimentos com categoria interna).

## Discriminação por conta (2026-11-21)

`GoalReservations::accountGoals` agrega por meta apenas os movimentos completed da conta. O detalhe da conta expõe `goalReservations`, com reservado, utilizado e contribuído líquido; o frontend apresenta a secção “Metas nesta conta”. A query usa EXISTS para pertença/permissão da meta, sem multiplicar valores por memberships duplicadas. Totais da conta incluem reservas privadas, mas os nomes/IDs dessas metas não são revelados. Sem nova migration.

## Eliminação assistida

`Services/GoalDeletion` fornece o contexto prévio à eliminação e a ação atómica `release-and-cancel`. A ação liberta o remanescente nas contas de origem através de withdrawals do ledger e marca canceled, preservando o histórico e as despesas reais. Permite também encerrar uma meta completed sem perder completedFunding. Pendentes devem ser resolvidos antes. Ver contrato e permissões em `docs/api/financial-goals.md`. DELETE físico mantém a regra de inexistência de histórico.

## Atividade: compatibilidade dos valores monetários

Os eventos de reservas guardam `amount`, `goalId`, `currencyId` e `amountFallback`. O leitor aceita também os eventos já gravados sem fallback, incluindo os gerados pela versão anterior do ledger: formata com a moeda disponível sem lançar Undefined array key. Ver regras de recurso em `support.md`. Não exige alterar logs existentes nem executar migrations.
