# Financial goal reservations (CM-120 / CM-250)

Branch BE: `feature/CM-120`. Frontend: `feature/CM-250`, issue `cashmanager-web#250`.

## Rules implemented

- New goals use `funding_mode=reservation`. Existing goals default to `legacy` and keep their balances/history until explicitly reconciled.
- A goal can reserve across several accounts, all in the goal's currency. No implicit currency conversion or transfer between accounts.
- `contribution` reserves; `withdrawal` releases; `usage` consumes a reserve through an actual expense. Reserve/release never create account transactions.
- For each account: `availableAmount = balance - reservedAmount`, across every reservation goal. Existing ordinary spending can produce a shortfall; this is reported, not hidden or clamped to zero. New reservations cannot exceed available money.
- Pending reserve/release entries have no effect until confirmed. Confirmation on or after the due date rechecks available money and permissions; confirmation is idempotent. Pending usage is not supported.
- Creating a usage creates one completed, categorized expense. Linking an existing completed expense consumes a chosen portion of its unallocated amount without debiting again. Multiple goals may allocate separate portions of the same expense, bounded by the expense total and each account/goal reserve.
- Usage creation cannot exceed the reserve or actual account balance. To fund only part of a larger purchase, record the full normal expense and link the reserved portion afterwards.
- Accounts must be active for reserve/usage. Remaining reservations can be released from inactive accounts.
- Funding/progress is completed contributions minus releases, equivalently current reserve plus usage. Releasing and reserving the same money does not inflate funding. Gross contribution totals remain movement-history statistics, not progress.
- Completion is manual and requires sufficient funding and no pending movements. It does not create a payment or release money. `completed_funding` snapshots funding; a completed goal can use or release remaining reserves. Reopening clears the snapshot. Reopen before changing the completed target.
- Cancellation requires no reserve and no pending entries. Goals/accounts with reservation history cannot be deleted; goal cancellation/account deactivation preserve it. Currency changes are blocked after relevant history.
- Completed reserve/release entries are immutable. Corrections use explicit opposing movements. Pending entries can be edited/deleted.
- Deleting a usage **unlinks it**, preserves its expense and restores the reservation; this can expose a shortfall. Unlink usages before editing/deleting an expense or recording a reversal/refund. There is no automatic refund or reimbursement allocation.
- Both account and goal permissions apply. Candidate expenses/associations require account `viewAccount`, `viewTransaction` and `createTransaction`; goal writes use the existing goal-movement permissions. Names of inaccessible goals are not disclosed from shared transaction details.
- User/account/goal locks serialize writes. HTTP creation requires a stable `request_key`; receipts survive unlink/deletion so replaying an old purchase request cannot create another expense. Recurring occurrences use a unique recurring ID/date pair.
- Recurring goal expenses generate reserves; recurring goal revenues generate releases. Missing funds fail without advancing generation. Monthly savings estimates are separate from income/expenses. Real account reports contain only real transactions.

## API

All paths below use `/api/v1`, Sanctum and the existing locale middleware.

- `GET financial-goals/{id}/reservations`: funding mode, reserve, usage, funding, completion snapshot and authorized account summaries (balance, total reserve, available, shortfall and reserve for this goal).
- `GET financial-goals/{id}/eligible-expenses?account_id=...&page=1&search=...`: completed, non-future expenses in that account, 20 per page, with `unallocatedAmount` and `hasMore`. Zero-unallocated items are not selectable in the frontend. Previous internal goal expenses are excluded.
- `POST financial-goal-transactions`: existing fields plus `type=usage`, optional `transaction_id` to link an expense or `category_id` to create one, and mandatory `request_key` for reservation-mode goals. Monetary inputs use at most two decimal places. Body cannot assign recurring identities.
- Existing movement update/delete/confirm routes apply the rules above. Completed usage deletion never deletes the expense.
- Existing goal complete/cancel/reset routes apply the lifecycle rules above.
- Validation inside writes returns HTTP 422 with field `errors`; authorization/not-found remain HTTP 403/404.
- Goal responses expose `fundingMode`, `reservedAmount`, `usedAmount`, `fundedAmount`, `completedFunding`; account responses add reserved/available/shortfall amounts. Transaction detail/list responses expose only visible `goalUsages`.

## Existing data: explicit conversion

Conversion must only be confirmed when the old internal movements represent money **still held in the listed accounts**. If that money was spent or moved elsewhere, reconcile the actual situation first. No automatic bulk conversion is run by the schema migration.

1. `GET financial-goals/{id}/reservation-conversion` previews account balances before/after restoring the old internal net contribution, the corresponding reserve and a SHA-256 preview token.
2. Verify the displayed accounts and the actual location of the money.
3. `POST` to the same path with `{ "confirm": true, "token": "<preview token>" }` explicitly accepts that interpretation. Any changed snapshot requires another preview.
4. The converter validates all old transaction links, types, statuses, internal categories, amounts, currency, permissions, per-account nonnegative net reserves and reconciliation with the goal's contributed total. Inconsistencies are rejected for manual review.
5. In one transaction it stores the complete original goal, movements, account transactions and account balances in `financial_goal_conversions.snapshot`, attaches each movement directly to its account, removes the reconciled internal account transactions, restores their net amount to account balances and switches the goal to reservations. Existing reports then exclude those internal movements without special-case filters.
6. Every existing transaction used by a legacy goal must remain linked to exactly one goal movement. The converter rejects broken or shared links rather than guessing their meaning. Repeating conversion cannot adjust balances a second time.

Keep a database backup before conversion. An audit snapshot is evidence for reconciliation; do not blindly overwrite an account balance from it after newer transactions exist. Converted/new goals prevent schema rollback. Recovery requires a coordinated backup restore or a separately reviewed reconciliation; there is no destructive automatic rollback.

## Delivery and verification

Deliver the schema migration and backend together before enabling the frontend flow. Apply the additive migration in a coordinated deployment, after backup and a staging rehearsal:

```sh
php artisan migrate --path=Modules/FinancialGoal/Database/Migrations/2026_11_01_180000_add_goal_reservations.php
```

The migration changes goal amount precision to two decimal places, extends the ledger, creates request receipts and conversion audit tables, and replaces `financial_goal_transaction_view`. It does not convert user data. Legacy goal mutations are blocked with a conversion explanation until reconciled.

Focused checks:

```sh
php artisan test --filter='GoalReservationsTest|TranslationCatalogueTest|PersonalCategoriesTest|RecurringSummaryTest|AccountResumeTest'
```

Tests use an isolated in-memory SQLite database and apply the new migration there. Rehearse the DDL on the target MySQL version before production rollout. Browser verification uses synthetic data only; no real accounts or payments are touched.
