# Base de dados — tabelas e views

MySQL 8. As migrations vivem em `Modules/*/Database/Migrations` (e em `database/migrations` para as tabelas base do Laravel). **Muitas migrations também semeiam dados** (permissões, roles, categorias por defeito, superadmin), mas os tipos/layouts de email e os tipos de notificação precisam de configuração adicional; `migrate` não garante todos os fluxos funcionais. O `DatabaseSeeder` de raiz está vazio.

## Mapa por domínio

| Domínio | Tabelas | Views |
|---|---|---|
| Utilizadores | `users`, `password_reset_tokens`, `sessions`, `personal_access_tokens`, `user_preferences` | — |
| RBAC global | `roles`, `permissions`, `role_permissions`, `role_user` | — |
| RBAC partilhado | `shared_roles`, `shared_permissions`, `shared_permission_roles` | — |
| Contas | `accounts`, `account_users`, `account_user_invites`, `transactions` | `accounts_view`, `transactions_view`, `account_basic_view` |
| Categorias | `categories` | — |
| Moedas/idiomas | `currencies`, `languages` | — |
| Dívidas | `debts`, `debts_user`, `debt_user_invites`, `debt_payments` | `debts_view`, `debt_payments_view`, `debt_basic_view`, `user_debt_payment_view` |
| Metas | `financial_goals`, `financial_goal_users`, `financial_goal_user_invites`, `financial_goal_transactions`, `financial_goal_request_receipts`, `financial_goal_reservation_rollouts`, `financial_goal_reservation_rollout_events`, `financial_goal_neutralized_transactions` | `financial_goal_view`, `financial_goal_basic_view`, `financial_goal_transaction_view`, `user_financial_goal_contributions_view` |
| Recorrentes | `recurring_transactions` | — |
| Amigos | `friendships`, `friendship_requests` | — |
| Notificações | `notification_types`, `notification_keywords`, `notification_type_keywords`, `notifications`, `user_notification_preferences`, `broadcast_notifications` | — |
| Emails | `email_types`, `email_type_keywords`, `email_layouts` | — |
| Atividade | `activity_logs` | — |
| Ativos | `assets` | `asset_search_view` |

## Relações principais

```
users ─┬─< account_users >── accounts ──< transactions >── categories
       │                       │  └── currencies
       ├─< debts_user >──── debts ──< debt_payments ──► transactions
       ├─< financial_goal_users >── financial_goals ──< financial_goal_transactions ──► accounts / transactions
       ├─< recurring_transactions ──► accounts, categories, (morph) debts | financial_goals
       ├── user_preferences ──► currencies, languages
       ├─< friendships / friendship_requests (sender_id, receiver_id)
       ├─< notifications ──► notification_types (type_code)
       └─< role_user >── roles ──< role_permissions >── permissions
membros (*_users) ──► shared_roles ──< shared_permission_roles >── shared_permissions
```

## Views SQL — o que calculam

As views são lidas por modelos `*View` (read-only), sobretudo em DataTables e detalhes. As listagens de contas/transações já consultam modelos e relações Eloquent, embora detalhes/agregados continuem a usar views. Nas consultas legadas, a membership é exposta como string `user_ids`/`userIds` (GROUP_CONCAT) e filtrada com `FIND_IN_SET`.

| View | Conteúdo |
|---|---|
| `accounts_view` | conta + moeda, nº de transações (todas), Σ receitas e Σ despesas completed, nomes/ids dos membros. INNER JOIN em moedas e users: uma conta sem moeda ou sem membros não aparece. Não aplica a exclusão das transações neutralizadas das metas |
| `transactions_view` | transação + categoria (nome/ícone/cor/tipo) + user + conta + moeda. INNER JOIN em categorias: transações sem categoria não aparecem |
| `account_basic_view` | id, nome, tipo, `user_ids`, active |
| `debts_view` | dívida + moeda, nº de pagamentos, Σ pagos completed, juros pagos, membros |
| `debt_payments_view` | pagamento + dívida + user + conta (via transação) + moeda |
| `debt_basic_view` | `target`, `current_amount`, estado, moeda, taxa, mensalidade, `user_ids` |
| `user_debt_payment_view` | por (user, dívida): `totalPaid` sem juros, `shared_role_id` |
| `financial_goal_view` | meta + Σ contribuições e Σ withdrawals completed, membros |
| `financial_goal_basic_view` | `target`, `current_amount`, `user_ids` |
| `financial_goal_transaction_view` | movimento + conta (`COALESCE(fgt.account_id, t.account_id)`), `transactionId`, `expenseCreated`, `balanceBefore` acumulado |
| `user_financial_goal_contributions_view` | por (user, meta): Σ contribuições completed |
| `asset_search_view` | id, ticker, nome (de `fundamentals_json.shortName`), logo, símbolo da moeda |

Para alterar uma view, cria-se uma nova migration com `DROP VIEW IF EXISTS` + `CREATE VIEW` (exemplo: `2026_11_02_210000` redefine `financial_goal_transaction_view`).

## Valores guardados (desnormalizados)

| Coluna | Mantida por |
|---|---|
| `accounts.balance` | `TransactionRepository` (adjust/update/reverse), `MigrateGoalReservations` |
| `debts.paid_amount`, `months_paid` | `DebtPaymentRepository` |
| `financial_goals.contributed_amount` | `GoalReservations::sync()` |
| `recurring_transactions.last_generated_at` | `RecurringTransactionGenerator` |

Quem alterar transações diretamente na BD tem de manter estas colunas coerentes.
