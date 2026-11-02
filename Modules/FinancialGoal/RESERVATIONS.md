# Financial goal reservations (CM-120 / CM-250)

Branch BE: `feature/CM-120`. Frontend: `feature/CM-250`, issue `cashmanager-web#250`.

## Rules implemented

- New goals use `funding_mode=reservation`. Existing goals first default to `legacy`; the deployment data migrations recover their account associations and automatically convert their reconciled history to reservations.
- A goal can reserve across several accounts, all in the goal's currency. No implicit currency conversion or transfer between accounts.
- `contribution` reserves; `withdrawal` releases; `usage` consumes a reserve through an actual expense. Reserve/release never create account transactions.
- For each account: `availableAmount = balance - reservedAmount`, across every reservation goal. Existing ordinary spending can produce a shortfall; negative available amounts are displayed without warning messages or clamping to zero. Account balances and available money are informational: zero/negative balances and reservations above the balance never block movements.
- Pending reserve/release entries have no effect until confirmed. Confirmation on or after the due date rechecks permissions and the goal reserve for releases; confirmation is idempotent. Pending usage is not supported.
- Creating a usage creates one completed, categorized expense. Linking an existing completed expense consumes a chosen portion of its unallocated amount without debiting again. Multiple goals may allocate separate portions of the same expense, bounded by the expense total and each account/goal reserve.
- Usage creation cannot exceed the goal reserve in the selected account; it may make the account balance negative. To fund only part of a larger purchase, record the full normal expense and link the reserved portion afterwards.
- Accounts must be active for reserve/usage. Remaining reservations can be released from inactive accounts.
- Funding/progress is completed contributions minus releases, equivalently current reserve plus usage. Releasing and reserving the same money does not inflate funding. Gross contribution totals remain movement-history statistics, not progress.
- Completion is manual and requires sufficient funding and no pending movements. It does not create a payment or release money. `completed_funding` snapshots funding; a completed goal can use or release remaining reserves. Reopening clears the snapshot. Reopen before changing the completed target.
- Cancellation requires no reserve and no pending entries. Goals/accounts with reservation history cannot be deleted; goal cancellation/account deactivation preserve it. Currency changes are blocked after relevant history.
- Completed reserve/release entries are immutable. Corrections use explicit opposing movements. Pending entries can be edited/deleted.
- Deleting a usage **unlinks it**, preserves its expense and restores the reservation; this can expose a shortfall. Unlink usages before editing/deleting an expense or recording a reversal/refund. There is no automatic refund or reimbursement allocation.
- Both account and goal permissions apply. Candidate expenses/associations require account `viewAccount`, `viewTransaction` and `createTransaction`; goal writes use the existing goal-movement permissions. Names of inaccessible goals are not disclosed from shared transaction details.
- User/account/goal locks serialize writes. HTTP creation requires a stable `request_key`; receipts survive unlink/deletion so replaying an old purchase request cannot create another expense. Recurring occurrences use a unique recurring ID/date pair.
- Recurring goal expenses generate reserves; recurring goal revenues generate releases. Releases above the goal reserve fail without advancing generation. Monthly savings estimates are separate from income/expenses. Real account reports contain only real transactions.

## API

All paths below use `/api/v1`, Sanctum and the existing locale middleware.

- `GET financial-goals/{id}/reservations`: funding mode, reserve, usage, funding, completion snapshot and authorized account summaries (balance, total reserve, available, shortfall and reserve for this goal).
- `GET financial-goals/{id}/eligible-expenses?account_id=...&page=1&search=...`: completed, non-future expenses in that account, 20 per page, with `unallocatedAmount` and `hasMore`. Zero-unallocated items are not selectable in the frontend. Previous internal goal expenses are excluded.
- `POST financial-goal-transactions`: existing fields plus `type=usage`, optional `transaction_id` to link an expense or `category_id` to create one, and mandatory `request_key` for reservation-mode goals. Monetary inputs use at most two decimal places. Body cannot assign recurring identities.
- Existing movement update/delete/confirm routes apply the rules above. Completed usage deletion never deletes the expense.
- Existing goal complete/cancel/reset routes apply the lifecycle rules above.
- Validation inside writes returns HTTP 422 with field `errors`; authorization/not-found remain HTTP 403/404.
- Goal responses expose `fundingMode`, `reservedAmount`, `usedAmount`, `fundedAmount`, `completedFunding`; account responses add reserved/available/shortfall amounts. Transaction detail/list responses expose only visible `goalUsages`.

## Existing data: automatic conversion during deployment

The follow-up migration `2026_11_02_180000_backfill_financial_goal_accounts.php` automatically copies each legacy movement's historical account from `transactions.account_id` into `financial_goal_transactions.account_id`, including pending contributions and withdrawals. It runs after the schema migration, also on environments where that migration has already been applied. It fills only missing associations with a valid existing account and preserves existing assignments, amounts, statuses, transaction links, balances and funding mode. Repeating it is safe; rollback keeps recovered associations.

The additional metadata migration `2026_11_02_185000_backfill_financial_goal_request_receipts.php` fills missing `request_key`/`request_hash` values for every existing financial-goal movement, including pending movements and entries already using reservations, and creates matching `financial_goal_request_receipts` rows (`entry_id`, goal, user, key and hash). Missing keys use the stable namespace `historical-goal-entry:<entry ID>`. Missing hashes are SHA-256 fingerprints of a stable request reconstructed from the stored movement and historical account/expense; this cannot recover the exact original HTTP payload. Existing valid keys/hashes take priority and are preserved, including recovery from an existing receipt. Conflicting ownership, keys or hashes abort the metadata transaction instead of overwriting receipts. Historical timestamps, account transactions, balances and goal totals are unchanged. Deleted-movement receipts are retained and rollback does not remove replay protection.

The conversion service also runs this metadata backfill before its financial transaction, so explicitly executing only the `190000` migration file still fills all receipts. Receipt metadata can therefore be completed even when financial conversion later fails; it does not resolve a total mismatch or a negative per-account reservation. Both phases are individually transactional and idempotent. Run deployments with application/recurring writes paused.

Recovered legacy movements remain outside the new reservation/available calculations between the association migration and accounting conversion, so their old account debits are not deducted twice. The final migration, `2026_11_02_190000_convert_legacy_goal_reservations.php`, performs the accounting conversion automatically for every legacy goal, including completed, cancelled and empty goals. It does not require per-user confirmation. This replaces the earlier manual-conversion rollout by explicit user decision on 2026-11-02.

If a historical transaction/account is missing, or a goal has a contributed total without movement history, the migration cannot infer the source. Review those cases explicitly; the converter rejects inconsistent history. This read-only query lists missing account assignments and funded goals without movements:

```sql
SELECT fg.id, fg.name, fg.contributed_amount,
       COUNT(fgt.id) AS movement_count,
       SUM(CASE WHEN fgt.id IS NOT NULL AND fgt.account_id IS NULL THEN 1 ELSE 0 END) AS unassigned_movements
FROM financial_goals fg
LEFT JOIN financial_goal_transactions fgt ON fgt.financial_goal_id = fg.id
WHERE fg.funding_mode = 'legacy'
GROUP BY fg.id, fg.name, fg.contributed_amount
HAVING unassigned_movements > 0
    OR (movement_count = 0 AND fg.contributed_amount <> 0);
```

The conversion validates old transaction links, types, statuses, internal categories, amounts, currency, recurring identities, account assignments, per-account nonnegative net reserves and reconciliation with each goal's contributed total. A missing origin or inconsistent total aborts the entire conversion transaction and reports the goal ID. All converted goals, balances, removed account transactions and audit writes roll back together. Repair the historical inconsistency before rerunning; the migration never invents an account or skips a failed goal silently.

For each reconciled goal, it:

1. Saves the complete original goal, movements, account transactions and account balances in `financial_goal_conversions.snapshot`. System conversions have actor `user_id=0`, source `migration` and balance policy `restore_internal_movements`; no logged-in user or account ownership is required by the deployment migration.
2. Attaches each movement directly to the account from its original transaction and preserves pending/completed status, amount, date and recurring identity.
3. Removes only the linked internal goal account transactions. Real expenses and unrelated transactions remain untouched. Historical copies remain in the audit snapshot.
4. Restores the old net completed contributions to each account balance, reversing old withdrawal credits as part of the same net calculation. Pending movements do not change balances. This is the user-approved policy: existing contributions should behave like the new model, which does not debit the account when reserving.
5. Switches the goal to reservations without changing its contributed total, lifecycle status or target. Completed goals preserve their funding snapshot and remain able to use/release their reserves. New reservations/usage work immediately after migration.

All old goals are converted in one database transaction; repeating the migration skips converted goals and cannot restore balances twice. Existing reservation goals and their real expenses are left untouched. The old authenticated preview/token endpoint remains available for controlled diagnostics/compatibility; it retains all user permissions and confirmation checks and cannot enable the privileged migration path through request parameters. It is no longer the normal rollout flow.

Keep a database backup before conversion. An audit snapshot is evidence for reconciliation; do not blindly overwrite an account balance from it after newer transactions exist. Converted/new goals prevent schema rollback. Recovery requires a coordinated backup restore or a separately reviewed reconciliation; there is no destructive automatic rollback.

## Delivery and verification

Deliver the schema migration and backend together before enabling the frontend flow. Apply the additive migration in a coordinated deployment, after backup and a staging rehearsal:

```sh
php artisan migrate \
  --path=Modules/FinancialGoal/Database/Migrations/2026_11_01_180000_add_goal_reservations.php \
  --path=Modules/FinancialGoal/Database/Migrations/2026_11_02_180000_backfill_financial_goal_accounts.php \
  --path=Modules/FinancialGoal/Database/Migrations/2026_11_02_185000_backfill_financial_goal_request_receipts.php \
  --path=Modules/FinancialGoal/Database/Migrations/2026_11_02_190000_convert_legacy_goal_reservations.php
```

The migration changes goal amount precision to two decimal places, extends the ledger, creates request receipts and conversion audit tables, and replaces `financial_goal_transaction_view`. The second migration recovers account associations; the third fills request metadata and durable receipts; the fourth automatically converts all reconciled legacy goals, restores the original internal net debits and makes them usable in the new flow. Do not enable the new frontend if conversion fails. All data migrations must be included; applying only the schema/association migrations leaves the intermediate legacy state. Pause application/recurring writes during deployment, rehearse against a production copy and keep the backup and audit records.

If conversion fails during total reconciliation, inspect the affected goal without changing data:

```sh
php artisan goals:inspect-reservations 1
```

The command prints stored funding, completed contributions minus withdrawals, their difference and per-account contributions/withdrawals/net reserves/pending counts. It reports missing historical origins and negative per-account net reserves, and returns exit code 1 for these discrepancies (or an unknown goal). Matching totals do not validate every conversion requirement. Errors during reconciliation now include the exact stored/calculated amounts and difference, or the account ID and its negative net reserve. These checks are independent of current account balances. Do not infer that missing receipt metadata caused a total mismatch; review the displayed values first.

Focused checks:

```sh
php artisan test --filter='GoalReservationsTest|TranslationCatalogueTest|PersonalCategoriesTest|RecurringSummaryTest|AccountResumeTest'
```

Tests use an isolated in-memory SQLite database and apply the new migration there. Rehearse the DDL on the target MySQL version before production rollout. Browser verification uses synthetic data only; no real accounts or payments are touched.
