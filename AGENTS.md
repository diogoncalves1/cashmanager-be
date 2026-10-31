# CashManager Backend Agent Notes

Read this file before working on this repository. It is a compact map of the project so future prompts do not require scanning the whole codebase first.

## Project

- Backend for CashManager.
- Laravel 12 / PHP 8.2 application.
- Uses `nwidart/laravel-modules` with domain modules under `Modules/`.
- API authentication uses Laravel Sanctum.
- Frontend/admin assets use Vite, Tailwind CSS 4, Blade views, and package scripts from `package.json`.
- DataTables are built with Yajra packages.
- Telescope is installed.

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

- Start each prompt by reading this file.
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

- Currency rates: `CurrencyServiceProvider` schedules `currency:fetch-daily` daily at 10:30 in `Europe/Lisbon`, following Portugal mainland daylight saving changes independently of the application timezone. It uses overlap and single-server locks. The existing minutely `artisan schedule:run` cron handles this command as well as recurring transactions; do not add a separate currency cron. Command output appends to `storage/logs/currency-rates.log`; the API key is configured with `CURRENCY_API_KEY`.
- Recurring transactions: `Modules/RecurringTransaction/Services/RecurringTransactionGenerator.php` generates due items. Unlinked recurring transactions create a normal `Transaction` through `TransactionRepository`. Linked debt recurring transactions create a `DebtPayment` through `DebtPaymentRepository`, which creates the underlying account transaction and updates debt paid amounts when completed. Linked financial-goal recurring transactions create a `FinancialGoalTransaction` through `FinancialGoalTransactionRepository`, which creates the underlying account transaction and updates contributed amounts when completed. For financial goals, recurring `expense` maps to `contribution` and recurring `revenue` maps to `withdrawal`.

- Dashboard overview: `DashboardWidgetsRepository` adds account statistics, three pending debts ordered by due date, three active goals ordered by progress and a recurring summary to `/api/v1/dashboard-overview`. Preserve each domain's account/member visibility boundaries; do not return full DataTable lists or issue internal HTTP calls. Recurring totals remain separate per currency and the upcoming payload is limited to five items. Focused checks: `tests/Unit/DashboardWidgetsTest.php` and `tests/Unit/RecurringPresentationTest.php`.
- Recurring presentation: `next_occurrence` follows `nextDateAfter` and is null beyond the end date. The show route uses the recurring policy; table action flags also use that policy. API amounts are numeric and category names are localized through `CategoryResource`.
- Recurring validation: store/update inherit `RecurringTransactionRequest`, which always returns JSON 422 with `message` and field `errors`, including without an Accept header. Copy lives in `recurringtransaction::validation` (pt/en), registered explicitly in the module provider; routes use `setlocale`. Unlinked form payloads may send null link fields. Creating a recurring transaction requires an account; titles respect the 255-character database limit.
- Translation catalogues live in `Modules/*/Resources/lang/{pt,en}`. `tests/Unit/TranslationCatalogueTest.php` checks matching keys/placeholders, module references (including dynamic prefixes), and runtime registration. Use `friends::messages.friendship-requests` and `debts::attributes.debt-payments` as their canonical namespaces.
- API login validates email/password and handles `ValidationException` with its HTTP status and field errors; invalid credentials are HTTP 422, while unverified emails remain HTTP 403. `tests/Feature/ApiLoginValidationTest.php` uses an isolated in-memory SQLite database.
