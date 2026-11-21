# CashManager Backend Agent Notes

Read this file before working on this repository. It is a compact map of the project so future prompts do not require scanning the whole codebase first.

## Project

- Backend for CashManager.
- Laravel 12 / PHP 8.2 application.
- Uses `nwidart/laravel-modules` with domain modules under `Modules/`.
- API authentication uses Laravel Sanctum.
- Frontend/admin assets use Vite, Tailwind CSS 4, Blade views, and package scripts from `package.json`.
- API lists use ModulePaginate/ModuleFilter/ModuleSearch. Yajra DataTables are for web backoffice only.
- Telescope is installed.

## Full Documentation

- Detailed docs live inside this repo at `docs/` (`/Users/diogogoncalves/development/CashManager/Back/CashManager/docs/`). Start with `docs/README.md`, then open only the relevant file:
  - `architecture/` — layers, auth & shared permissions, cross-cutting services, `modules/` (business rules per domain), `database/` (tables, SQL views, seeded permissions), `known-issues.md`.
  - `api/` — REST reference per domain (bodies, responses, errors); `api/README.md` has envelope/pagination conventions.
  - `process/` — `git-workflow.md` (branch `<type>/<description>`, conventional commits, commit/push only when asked), `code-conventions.md`, `development-guide.md`.
  - `intents/`, `specs/`, `plans/` — planning artefacts (INT/SPEC/PLAN-nnn) with templates in each README.
- When a change alters a business flow, route, request/response or table, or fixes a listed bug, update the matching file in `docs/` as well as this file.

## Main Structure

- `app/`: shared Laravel app code, base controllers, auth requests, filters, repositories, middleware, view components.
- `routes/api.php`: base API routes, especially auth under `/api/v1`.
- `Modules/`: business domains. Each module usually contains `Entities`, `Http`, `Routes`, `Repositories`, `Database`, `Providers`, `Resources`, and `Tests`.
- `Modules/*/Routes/api.php`: module API routes, usually grouped under `/api/v1`.
- `Modules/*/Entities`: Eloquent models and DB views.
- `Modules/*/Http/Controllers`: HTTP/API controllers.
- `Modules/*/Http/Requests`: validation requests.
- `Modules/*/Http/Resources`: API resources and collections.
- `Modules/*/Repositories`: query and persistence logic.
- `Modules/*/Database/Migrations`: module migrations.
- `modules_statuses.json`: enabled/disabled module list.

## Active Domains

Enabled modules currently include:

- `Accounts`
- `ActivityLog`
- `ApiResponder`
- `Asset`
- `Category`
- `Currency`
- `DashboardCharts`
- `Debts`
- `EmailLayout`
- `FinancialGoal`
- `Friends`
- `Language`
- `Notification`
- `Permission`
- `RecurringTransaction`
- `SharedRoles`
- `User`
- `UserPreferences`

## Important API Areas

- Auth routes live in `routes/api.php` under `/api/v1`.
- Account routes live in `Modules/Accounts/Routes/api.php`.
- Transaction routes live in `Modules/Accounts/Routes/api.php` via `Route::apiResource('transactions', TransactionController::class)`.
- Friend and friendship-request routes live in `Modules/Friends/Routes/api.php`.
- Most protected API module routes use `auth:sanctum` and `setlocale`.

## Local Commands

- Install/setup: `composer run setup`
- Dev stack: `composer run dev`
- PHP tests: `composer test`
- Frontend dev: `npm run dev`
- Frontend build: `npm run build`
- Laravel Pint is available through Composer dependencies.

## Working Rules For Agents

- Start each prompt by reading this file, then `docs/README.md` when architecture context is needed.
- Do not scan the whole repository unless the task requires it.
- After this file, inspect only the module or files related to the request.
- When adding features, changing business flows, or discovering important project behavior, update this file with a short note before finishing.
- Keep new notes compact and practical so this file remains cheap to read on every prompt.
- Prefer `rg` / `rg --files` for targeted search.
- Follow the existing module layout and naming conventions.
- Keep changes scoped to the relevant module.
- Do not revert unrelated local changes. This repository may already contain user work in progress.
- For API changes, check the relevant route, controller, request, resource, repository, entity/model, and migration/view files.
- For behavior changes, add or update tests when the risk justifies it.
- Before finishing code changes, run the most focused useful verification available, usually `composer test` or a narrower artisan/phpunit command when practical.

## Current Context Notes

- The repository currently has uncommitted changes in several modules, including `Accounts`, `ActivityLog`, `Category`, `SharedRoles`, and `RecurringTransaction`.
- Treat those changes as user-owned unless they were made during the current task.
- `README.md` is minimal, so this file is the primary quick-start context for future agent work.

## Feature Notes

- Financial goals (CM-120/CM-250): all goals now use one reservation ledger, without runtime funding modes. Migration `230000_unify_all_goal_reservations` fixes mixed historical/direct entries even after 210000/220000 ran and drops `funding_mode`; apply 210000 then 230000 as documented in `Modules/FinancialGoal/PRODUCTION-ROLLOUT.md`. Convert the whole dataset atomically; never leave a previous-model fallback. Preserve goal movement amounts/statuses/dates; historical transactions supply accounts and actual balance adjustments, guarded per transaction against duplicate refunds. Raw goal-7 regression: 131.40 contributions - 10 withdrawals = 121.40 reserved, including 21 previously unassigned entries. `goals:reservation-rollout` previews without writes; unresolved origins block the migration. MySQL 8.0.40 isolated rehearsal passed.

- Transaction category validation on creation must target `TransactionRequest`, not every FormRequest: goal contributions/withdrawals reuse their own FormRequest with a server-assigned internal category. Regression coverage in `PersonalCategoriesTest` checks pending/completed goal movements and manual-selection rejection.

- Personal categories: `Modules/Category/README.md` documents the API and migration. API ownership comes from auth; limit 50 includes archived categories and serializes writes on the user row. `SelectableCategory` protects transaction/recurring assignments and preserves original shared/archived assignments on edits. Existing recurring generation keeps archived references. Focused tests: `php artisan test --filter=PersonalCategoriesTest`. Icon catalogue: `CategoryIcons::ALL` contains 112 names and must match the frontend catalogue and iconMap, including legacy `DollarSign`.

- Budgets is planning only; do not implement without a user request. Decisions/examples/BE-FE architecture are saved in the frontend repository at `/Users/diogogoncalves/development/CashManager/Front/CashManager/BUDGETS.md`, `BUDGETS-EXAMPLES.md`, and `BUDGETS-ARCHITECTURE.md`. Notification integration must enforce app-only delivery even with email preferences enabled; current `NotificationCreatedListener` can send email. Existing template keywords use `[$keyword]` with an unwrapped payload key.

- Currency rates: `CurrencyServiceProvider` schedules `currency:fetch-daily` daily at 01:00 in `Europe/Lisbon`, following Portugal mainland daylight saving changes independently of the application timezone. It uses overlap and single-server locks. The existing minutely `artisan schedule:run` cron handles this command as well as recurring transactions; do not add a separate currency cron. Command output appends to `storage/logs/currency-rates.log`; the API key is configured with `CURRENCY_API_KEY`.
- Recurring transactions: `Modules/RecurringTransaction/Services/RecurringTransactionGenerator.php` generates due items. Unlinked recurring transactions create a normal `Transaction` through `TransactionRepository`. Linked debt recurring transactions create a `DebtPayment` through `DebtPaymentRepository`, which creates the underlying account transaction and updates debt paid amounts when completed. Linked financial-goal recurring transactions create a `FinancialGoalTransaction` through `FinancialGoalTransactionRepository`, which creates the underlying account transaction and updates contributed amounts when completed. For financial goals, recurring `expense` maps to `contribution` and recurring `revenue` maps to `withdrawal`.

- Dashboard overview: `DashboardWidgetsRepository` adds account statistics, three pending debts ordered by due date, three active goals ordered by progress and a recurring summary to `/api/v1/dashboard-overview`. Preserve each domain's account/member visibility boundaries; do not return full DataTable lists or issue internal HTTP calls. Recurring totals remain separate per currency and the upcoming payload is limited to five items. Focused checks: `Modules/RecurringTransaction/Tests/Unit/RecurringSummaryTest.php` and `Modules/RecurringTransaction/Tests/Unit/RecurringPresentationTest.php`.
- Recurring presentation: `next_occurrence` follows `nextDateAfter` and is null beyond the end date. The show route uses the recurring policy; table action flags also use that policy. API amounts are numeric and category names are localized through `CategoryResource`.
- Recurring validation: store/update inherit `RecurringTransactionRequest`, which always returns JSON 422 with `message` and field `errors`, including without an Accept header. Copy lives in `recurringtransaction::validation` (pt/en), registered explicitly in the module provider; routes use `setlocale`. Unlinked form payloads may send null link fields. Creating a recurring transaction requires an account; titles respect the 255-character database limit.
- Translation catalogues live in `Modules/*/Resources/lang/{pt,en}`. `tests/Unit/TranslationCatalogueTest.php` checks matching keys/placeholders, module references (including dynamic prefixes), and runtime registration. Use `friends::messages.friendship-requests` and `debts::attributes.debt-payments` as their canonical namespaces.
- API login validates email/password and handles `ValidationException` with its HTTP status and field errors; invalid credentials are HTTP 422, while unverified emails remain HTTP 403. `tests/Feature/ApiLoginValidationTest.php` uses an isolated in-memory SQLite database.

- Recurring list summary: GET `/api/v1/recurrings?includeSummary=1` adds `summary` alongside rows/categories. `RecurringSummaryRepository` is shared with the dashboard; totals cover every visible account membership, ignore table filters/pagination, exclude soft-deleted rows and cap upcoming previews at five. Omit the flag for table-only consumers. Focused integration checks: `Modules/RecurringTransaction/Tests/Feature/RecurringListSummaryTest.php`.

- Account Global Resume: authenticated GET `/api/v1/account/resume` supports `period=3m|12m|ytd|all` and an optional visible-account currency code. `AccountResumeRepository` returns current balances, period totals/monthly series/categories and eight latest movements in one response. Scope by membership AND `viewAccount`, avoid duplicate account/permission rows, keep currencies separate, exclude pending/future transactions from completed totals. No migrations/dependencies. Tests: `Modules/Accounts/Tests/Feature/AccountResumeTest.php`.

- Financial-goal movement pagination uses membership/permission EXISTS without joining the aggregate goal view, preserving count compatibility.

- Documentation review (2026-11-20): `docs/README.md` now routes to setup/configuration, module/financial-flow maps, operations and testing. `api/route-inventory.md` is generated from `artisan route:list --json`; `process/documentation.md` explains regeneration/link checks and that docs are versioned inside this Git repo. Current local accounts/transactions lists use custom pagination; account Gate/policy mismatches and filter/stat differences are documented in `architecture/known-issues.md`. Do not assume the refactor is complete from class names alone.

- Listing architecture: `docs/architecture/module-listings.md`. Concrete classes live in each module's Paginates/Filters/Searches; base classes in app/. Lists return pagination + optional meta. Never use DataTables in API controllers. Keep each domain's tests in Modules/<Module>/Tests/{Feature,Unit}, registered in phpunit.xml; global tests are cross-cutting only.
- GitHub workflow: all issues must belong to Cash Manager project #2 and have its Version field set to the intended delivery iteration (currently Version 1.6.2 for this migration). See docs/process/git-workflow.md. Prepare PRs and wait for the owner's explicit confirmation before merging; never enable auto-merge.

- Opening a PR must immediately move its associated issues to Code Complete in the Cash Manager GitHub project. Verify the project Status field; this does not authorize merge or issue closure.

- PR targets must be the matching delivery branch `develop/vX.Y.Z` (Version 1.6.2 → `develop/v1.6.2`), including dependent changes. Link every issue to its actual code branch in GitHub Development; when the branch already has a PR, link that PR and verify its head branch. Text references alone are insufficient.

- Account goal breakdown (2026-11-21): account detail returns `goalReservations` via `GoalReservations::accountGoals` (completed entries scoped to account, EXISTS membership + viewFinancialGoal); private goal identities stay hidden while account totals include their reserves. Tests in GoalReservationsTest cover the 100 contribution / 20 withdrawal / 30 usage example, account separation and duplicate memberships. No new migration.

- Assisted goal deletion: GoalDeletion exposes deletion-context and POST release-and-cancel (bodyless). Locks user → sorted accounts → goal → entries, releases remaining reserves atomically using ledger withdrawals, preserves expenses/history/completedFunding, blocks pending/unreconciled entries and unauthorized origins. Physical DELETE still rejects history. No migration.

- Goal activity amounts: ActivityLogMessageResolver accepts missing amountFallback/initialTargetFallback in existing goal logs and missing goal/currency references. GoalReservations writes currencyId + amountFallback snapshots on new movement events. Regression: Modules/ActivityLog/Tests/Feature/GoalActivityMessagesTest.php (PT/EN endpoint and historical metadata); writer checks in GoalReservationsTest. No data migration/backfill.
