# Inventário de rotas registadas

Gerado em 2026-11-21 a partir de `php artisan route:list --json`: **300 registos**, dos quais **143 de API**. Um registo pode aceitar vários métodos HTTP.

Este é o mapa de registo da aplicação local, incluindo rotas de backoffice e pacotes. Não valida a execução dos handlers, permissões internas, dados ou configuração de produção. Contratos e exemplos: [convenções da API](README.md). Regeneração: [manutenção](../process/documentation.md).

Os nomes de middleware abaixo são os devolvidos pelo Laravel. `Authenticate:sanctum` corresponde a autenticação Sanctum; `Authenticate` sem argumento usa o guard por omissão. Uma rota sem esse middleware pode continuar a ter verificações internas.

## API / Accounts

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/account/resume` | [AccountResumeController](../../Modules/Accounts/Http/Controllers/Api/V1/AccountResumeController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/accounts` | [AccountController@index](../../Modules/Accounts/Http/Controllers/Api/V1/AccountController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/accounts` | [AccountController@store](../../Modules/Accounts/Http/Controllers/Api/V1/AccountController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/accounts/all` | [AccountController@allUser](../../Modules/Accounts/Http/Controllers/Api/V1/AccountController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/accounts/invitations-stats` | [AccountUserInviteController@getInvitationsStats](../../Modules/Accounts/Http/Controllers/Api/V1/AccountUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/accounts/received-invitations` | [AccountUserInviteController@getReceivedInvitations](../../Modules/Accounts/Http/Controllers/Api/V1/AccountUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/accounts/sent-invitations` | [AccountUserInviteController@getSentInvitations](../../Modules/Accounts/Http/Controllers/Api/V1/AccountUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/accounts/{account}` | [AccountController@destroy](../../Modules/Accounts/Http/Controllers/Api/V1/AccountController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/accounts/{account}` | [AccountController@show](../../Modules/Accounts/Http/Controllers/Api/V1/AccountController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PUT, PATCH | `/api/v1/accounts/{account}` | [AccountController@update](../../Modules/Accounts/Http/Controllers/Api/V1/AccountController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/accounts/{id}/accept` | [AccountUserInviteController@accept](../../Modules/Accounts/Http/Controllers/Api/V1/AccountUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/accounts/{id}/activity` | [AccountController@activity](../../Modules/Accounts/Http/Controllers/Api/V1/AccountController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/accounts/{id}/invite/{userId}` | [AccountUserInviteController@destroy](../../Modules/Accounts/Http/Controllers/Api/V1/AccountUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/accounts/{id}/invite/{userId}` | [AccountUserInviteController@invite](../../Modules/Accounts/Http/Controllers/Api/V1/AccountUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/accounts/{id}/leave` | [AccountUserController@leave](../../Modules/Accounts/Http/Controllers/Api/V1/AccountUserController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/accounts/{id}/revoke` | [AccountUserInviteController@revoke](../../Modules/Accounts/Http/Controllers/Api/V1/AccountUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/accounts/{id}/revoke-user/{userId}` | [AccountUserController@revokeUser](../../Modules/Accounts/Http/Controllers/Api/V1/AccountUserController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PATCH | `/api/v1/accounts/{id}/status` | [AccountController@status](../../Modules/Accounts/Http/Controllers/Api/V1/AccountController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PUT | `/api/v1/accounts/{id}/user-role/{userId}` | [AccountUserController@updateUserRole](../../Modules/Accounts/Http/Controllers/Api/V1/AccountUserController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/transactions` | [TransactionController@index](../../Modules/Accounts/Http/Controllers/Api/V1/TransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/transactions` | [TransactionController@store](../../Modules/Accounts/Http/Controllers/Api/V1/TransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/transactions/{id}/confirm` | [TransactionController@confirm](../../Modules/Accounts/Http/Controllers/Api/V1/TransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/transactions/{transaction}` | [TransactionController@destroy](../../Modules/Accounts/Http/Controllers/Api/V1/TransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/transactions/{transaction}` | [TransactionController@show](../../Modules/Accounts/Http/Controllers/Api/V1/TransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PUT, PATCH | `/api/v1/transactions/{transaction}` | [TransactionController@update](../../Modules/Accounts/Http/Controllers/Api/V1/TransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |

## API / Asset

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/asset` | [AssetController@index](../../Modules/Asset/Http/Controllers/Api/V1/AssetController.php) | `api` |
| GET, HEAD | `/api/v1/assets/search` | [AssetController@search](../../Modules/Asset/Http/Controllers/Api/V1/AssetController.php) | `api` |
| GET, HEAD | `/api/v1/assets/{id}` | [AssetController@show](../../Modules/Asset/Http/Controllers/Api/V1/AssetController.php) | `api` |
| GET, HEAD | `/api/v1/assets/{ticker}/show` | [AssetController@showByTicker](../../Modules/Asset/Http/Controllers/Api/V1/AssetController.php) | `api` |

## API / Autenticação e aplicação

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/emaillayout` | Closure | `api`, `Authenticate:api` |
| POST | `/api/v1/change-password` | [AuthController@changePassword](../../app/Http/Controllers/Api/V1/AuthController.php) | `api`, `Authenticate:sanctum` |
| POST | `/api/v1/login` | [AuthController@login](../../app/Http/Controllers/Api/V1/AuthController.php) | `api` |
| POST | `/api/v1/logout` | [AuthController@logout](../../app/Http/Controllers/Api/V1/AuthController.php) | `api`, `Authenticate:sanctum` |
| GET, HEAD | `/api/v1/me` | [AuthController@me](../../app/Http/Controllers/Api/V1/AuthController.php) | `api`, `Authenticate:sanctum` |
| POST | `/api/v1/register` | [AuthController@register](../../app/Http/Controllers/Api/V1/AuthController.php) | `api` |
| POST | `/api/v1/reset-change-password` | [AuthController@resetChangePassword](../../app/Http/Controllers/Api/V1/AuthController.php) | `api`, `Authenticate:sanctum` |
| POST | `/api/v1/reset-password` | [AuthController@resetPassword](../../app/Http/Controllers/Api/V1/AuthController.php) | `api` |
| POST | `/api/v1/verify-email` | [AuthController@verifyEmail](../../app/Http/Controllers/Api/V1/AuthController.php) | `api`, `Authenticate:sanctum` |

## API / Category

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/categories` | [CategoryController@index](../../Modules/Category/Http/Controllers/Api/CategoryController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/categories` | [CategoryController@store](../../Modules/Category/Http/Controllers/Api/CategoryController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/categories/{category}` | [CategoryController@destroy](../../Modules/Category/Http/Controllers/Api/CategoryController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/categories/{category}` | [CategoryController@show](../../Modules/Category/Http/Controllers/Api/CategoryController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PUT, PATCH | `/api/v1/categories/{category}` | [CategoryController@update](../../Modules/Category/Http/Controllers/Api/CategoryController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PATCH | `/api/v1/categories/{id}/archive` | [CategoryController@archive](../../Modules/Category/Http/Controllers/Api/CategoryController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |

## API / Currency

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/currencies` | [CurrencyController@index](../../Modules/Currency/Http/Controllers/Api/CurrencyController.php) | `api`, `SetLocale`, `Authenticate:sanctum` |
| POST | `/api/v1/currencies` | [CurrencyController@store](../../Modules/Currency/Http/Controllers/Api/CurrencyController.php) | `api`, `Authenticate`, `web` |
| GET, HEAD | `/api/v1/currencies/check-code` | [CurrencyController@checkCode](../../Modules/Currency/Http/Controllers/Api/CurrencyController.php) | `api`, `Authenticate`, `web` |
| GET, HEAD | `/api/v1/currencies/update-rates` | [CurrencyController@updateRates](../../Modules/Currency/Http/Controllers/Api/CurrencyController.php) | `api`, `Authenticate`, `web` |
| DELETE | `/api/v1/currencies/{currency}` | [CurrencyController@destroy](../../Modules/Currency/Http/Controllers/Api/CurrencyController.php) | `api`, `Authenticate`, `web` |
| GET, HEAD | `/api/v1/currencies/{currency}` | [CurrencyController@show](../../Modules/Currency/Http/Controllers/Api/CurrencyController.php) | `api`, `SetLocale`, `Authenticate:sanctum` |
| PUT, PATCH | `/api/v1/currencies/{currency}` | [CurrencyController@update](../../Modules/Currency/Http/Controllers/Api/CurrencyController.php) | `api`, `Authenticate`, `web` |

## API / DashboardCharts

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/dashboard-overview` | [DashboardChartsController@overview](../../Modules/DashboardCharts/Http/Controllers/Api/V1/DashboardChartsController.php) | `api`, `Authenticate:sanctum` |

## API / Debts

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/debt-payments` | [DebtPaymentController@index](../../Modules/Debts/Http/Controllers/Api/V1/DebtPaymentController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/debt-payments` | [DebtPaymentController@store](../../Modules/Debts/Http/Controllers/Api/V1/DebtPaymentController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/debt-payments/{debt_payment}` | [DebtPaymentController@destroy](../../Modules/Debts/Http/Controllers/Api/V1/DebtPaymentController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/debt-payments/{debt_payment}` | [DebtPaymentController@show](../../Modules/Debts/Http/Controllers/Api/V1/DebtPaymentController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PUT, PATCH | `/api/v1/debt-payments/{debt_payment}` | [DebtPaymentController@update](../../Modules/Debts/Http/Controllers/Api/V1/DebtPaymentController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/debt-payments/{id}/confirm` | [DebtPaymentController@confirm](../../Modules/Debts/Http/Controllers/Api/V1/DebtPaymentController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/debts` | [DebtController@index](../../Modules/Debts/Http/Controllers/Api/V1/DebtController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/debts` | [DebtController@store](../../Modules/Debts/Http/Controllers/Api/V1/DebtController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/debts/all` | [DebtController@allUser](../../Modules/Debts/Http/Controllers/Api/V1/DebtController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/debts/invitations-stats` | [DebtUserInviteController@getInvitationsStats](../../Modules/Debts/Http/Controllers/Api/V1/DebtUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/debts/received-invitations` | [DebtUserInviteController@getReceivedInvitations](../../Modules/Debts/Http/Controllers/Api/V1/DebtUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/debts/sent-invitations` | [DebtUserInviteController@getSentInvitations](../../Modules/Debts/Http/Controllers/Api/V1/DebtUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/debts/stats` | [DebtController@getStats](../../Modules/Debts/Http/Controllers/Api/V1/DebtController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/debts/{debt}` | [DebtController@destroy](../../Modules/Debts/Http/Controllers/Api/V1/DebtController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/debts/{debt}` | [DebtController@show](../../Modules/Debts/Http/Controllers/Api/V1/DebtController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PUT, PATCH | `/api/v1/debts/{debt}` | [DebtController@update](../../Modules/Debts/Http/Controllers/Api/V1/DebtController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/debts/{id}/accept` | [DebtUserInviteController@accept](../../Modules/Debts/Http/Controllers/Api/V1/DebtUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/debts/{id}/activity` | [DebtController@activity](../../Modules/Debts/Http/Controllers/Api/V1/DebtController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/debts/{id}/invite/{userId}` | [DebtUserInviteController@destroy](../../Modules/Debts/Http/Controllers/Api/V1/DebtUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/debts/{id}/invite/{userId}` | [DebtUserInviteController@invite](../../Modules/Debts/Http/Controllers/Api/V1/DebtUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/debts/{id}/leave` | [DebtUserController@leave](../../Modules/Debts/Http/Controllers/Api/V1/DebtUserController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/debts/{id}/mark-paid` | [DebtController@markPaid](../../Modules/Debts/Http/Controllers/Api/V1/DebtController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/debts/{id}/reset` | [DebtController@reset](../../Modules/Debts/Http/Controllers/Api/V1/DebtController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/debts/{id}/revoke` | [DebtUserInviteController@revoke](../../Modules/Debts/Http/Controllers/Api/V1/DebtUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/debts/{id}/revoke-user/{userId}` | [DebtUserController@revokeUser](../../Modules/Debts/Http/Controllers/Api/V1/DebtUserController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PUT | `/api/v1/debts/{id}/user-role/{userId}` | [DebtUserController@updateUserRole](../../Modules/Debts/Http/Controllers/Api/V1/DebtUserController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |

## API / FinancialGoal

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/financial-goal-transactions` | [FinancialGoalTransactionController@index](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/financial-goal-transactions` | [FinancialGoalTransactionController@store](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/financial-goal-transactions/{financial_goal_transaction}` | [FinancialGoalTransactionController@destroy](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goal-transactions/{financial_goal_transaction}` | [FinancialGoalTransactionController@show](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PUT, PATCH | `/api/v1/financial-goal-transactions/{financial_goal_transaction}` | [FinancialGoalTransactionController@update](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/financial-goal-transactions/{id}/confirm` | [FinancialGoalTransactionController@confirm](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goals` | [FinancialGoalController@index](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/financial-goals` | [FinancialGoalController@store](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goals/all` | [FinancialGoalController@allUser](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goals/invitations-stats` | [FinancialGoalUserInviteController@getInvitationsStats](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goals/received-invitations` | [FinancialGoalUserInviteController@getReceivedInvitations](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goals/sent-invitations` | [FinancialGoalUserInviteController@getSentInvitations](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goals/stats` | [FinancialGoalController@getStats](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/financial-goals/{financial_goal}` | [FinancialGoalController@destroy](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goals/{financial_goal}` | [FinancialGoalController@show](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PUT, PATCH | `/api/v1/financial-goals/{financial_goal}` | [FinancialGoalController@update](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/financial-goals/{id}/accept` | [FinancialGoalUserInviteController@accept](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goals/{id}/activity` | [FinancialGoalController@activity](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/financial-goals/{id}/cancel` | [FinancialGoalController@cancel](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/financial-goals/{id}/complete` | [FinancialGoalController@complete](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goals/{id}/deletion-context` | [GoalReservationsController@deletionContext](../../Modules/FinancialGoal/Http/Controllers/Api/V1/GoalReservationsController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goals/{id}/eligible-expenses` | [GoalReservationsController@expenses](../../Modules/FinancialGoal/Http/Controllers/Api/V1/GoalReservationsController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/financial-goals/{id}/invite/{userId}` | [FinancialGoalUserInviteController@destroy](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/financial-goals/{id}/invite/{userId}` | [FinancialGoalUserInviteController@invite](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/financial-goals/{id}/leave` | [FinancialGoalUserController@leave](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalUserController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/financial-goals/{id}/release-and-cancel` | [GoalReservationsController@releaseAndCancel](../../Modules/FinancialGoal/Http/Controllers/Api/V1/GoalReservationsController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/financial-goals/{id}/reservations` | [GoalReservationsController@context](../../Modules/FinancialGoal/Http/Controllers/Api/V1/GoalReservationsController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/financial-goals/{id}/reset` | [FinancialGoalController@reset](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/financial-goals/{id}/revoke` | [FinancialGoalUserInviteController@revoke](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalUserInviteController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/financial-goals/{id}/revoke-user/{userId}` | [FinancialGoalUserController@revokeUser](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalUserController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PUT | `/api/v1/financial-goals/{id}/user-role/{userId}` | [FinancialGoalUserController@updateUserRole](../../Modules/FinancialGoal/Http/Controllers/Api/V1/FinancialGoalUserController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |

## API / Friends

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/friendship-requests/received` | [FriendshipRequestController@listReceived](../../Modules/Friends/Http/Controllers/Api/FriendshipRequestController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/friendship-requests/sent` | [FriendshipRequestController@listSent](../../Modules/Friends/Http/Controllers/Api/FriendshipRequestController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/friendship-requests/{id}/accept` | [FriendshipRequestController@accept](../../Modules/Friends/Http/Controllers/Api/FriendshipRequestController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/friendship-requests/{id}/cancel` | [FriendshipRequestController@cancel](../../Modules/Friends/Http/Controllers/Api/FriendshipRequestController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/friendship-requests/{id}/decline` | [FriendshipRequestController@decline](../../Modules/Friends/Http/Controllers/Api/FriendshipRequestController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/friendship-requests/{id}/send` | [FriendshipRequestController@send](../../Modules/Friends/Http/Controllers/Api/FriendshipRequestController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/friendships` | [FriendshipController@listFriends](../../Modules/Friends/Http/Controllers/Api/FriendshipController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/friendships/blocked` | [FriendshipController@listBlocked](../../Modules/Friends/Http/Controllers/Api/FriendshipController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/friendships/stats` | [FriendshipRequestController@stats](../../Modules/Friends/Http/Controllers/Api/FriendshipRequestController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/friendships/{id}/block` | [FriendshipController@block](../../Modules/Friends/Http/Controllers/Api/FriendshipController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/friendships/{id}/remove` | [FriendshipController@remove](../../Modules/Friends/Http/Controllers/Api/FriendshipController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/friendships/{id}/unblock` | [FriendshipController@unblock](../../Modules/Friends/Http/Controllers/Api/FriendshipController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |

## API / Language

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/languages` | [LanguageController@index](../../Modules/Language/Http/Controllers/Api/V1/LanguageController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |

## API / Notification

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/notifications` | [NotificationController@index](../../Modules/Notification/Http/Controllers/Api/V1/NotificationController.php) | `api`, `Authenticate:sanctum` |
| GET, HEAD | `/api/v1/notifications/feed` | [NotificationController@feed](../../Modules/Notification/Http/Controllers/Api/V1/NotificationController.php) | `api`, `Authenticate:sanctum` |
| POST | `/api/v1/notifications/read-all` | [NotificationController@markAllAsRead](../../Modules/Notification/Http/Controllers/Api/V1/NotificationController.php) | `api`, `Authenticate:sanctum` |
| DELETE | `/api/v1/notifications/{id}` | [NotificationController@destroy](../../Modules/Notification/Http/Controllers/Api/V1/NotificationController.php) | `api`, `Authenticate:sanctum` |
| POST | `/api/v1/notifications/{id}/archive` | [NotificationController@markAsArchived](../../Modules/Notification/Http/Controllers/Api/V1/NotificationController.php) | `api`, `Authenticate:sanctum` |
| POST | `/api/v1/notifications/{id}/read` | [NotificationController@markAsRead](../../Modules/Notification/Http/Controllers/Api/V1/NotificationController.php) | `api`, `Authenticate:sanctum` |

## API / RecurringTransaction

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/recurrings` | [RecurringTransactionController@index](../../Modules/RecurringTransaction/Http/Controllers/Api/V1/RecurringTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/recurrings` | [RecurringTransactionController@store](../../Modules/RecurringTransaction/Http/Controllers/Api/V1/RecurringTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| POST | `/api/v1/recurrings/{id}/toggle` | [RecurringTransactionController@toggle](../../Modules/RecurringTransaction/Http/Controllers/Api/V1/RecurringTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| DELETE | `/api/v1/recurrings/{recurring}` | [RecurringTransactionController@destroy](../../Modules/RecurringTransaction/Http/Controllers/Api/V1/RecurringTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/recurrings/{recurring}` | [RecurringTransactionController@show](../../Modules/RecurringTransaction/Http/Controllers/Api/V1/RecurringTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| PUT, PATCH | `/api/v1/recurrings/{recurring}` | [RecurringTransactionController@update](../../Modules/RecurringTransaction/Http/Controllers/Api/V1/RecurringTransactionController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |

## API / SharedRoles

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/shared-permissions` | [SharedPermissionController@index](../../Modules/SharedRoles/Http/Controllers/Api/SharedPermissionController.php) | `api`, `SetLocale`, `Authenticate:sanctum` |
| GET, HEAD | `/api/v1/shared-permissions/check-code` | [SharedPermissionController@checkPermissionCode](../../Modules/SharedRoles/Http/Controllers/Api/SharedPermissionController.php) | `api`, `Authenticate`, `web`, `SetLocale` |
| GET, HEAD | `/api/v1/shared-roles` | [SharedRoleController@all](../../Modules/SharedRoles/Http/Controllers/Api/SharedRoleController.php) | `api`, `SetLocale`, `Authenticate:sanctum` |
| GET, HEAD | `/api/v1/shared-roles/check-code` | [SharedRoleController@checkRoleCode](../../Modules/SharedRoles/Http/Controllers/Api/SharedRoleController.php) | `api`, `Authenticate`, `web`, `SetLocale` |
| GET, HEAD | `/api/v1/shared-roles/paginated` | [SharedRoleController@index](../../Modules/SharedRoles/Http/Controllers/Api/SharedRoleController.php) | `api`, `SetLocale`, `Authenticate:sanctum` |

## API / User

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/api/v1/check-username` | [UserController@checkUsername](../../Modules/User/Http/Controllers/Api/UserController.php) | `api` |
| PUT | `/api/v1/me` | [UserController@updateSettings](../../Modules/User/Http/Controllers/Api/UserController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |
| GET, HEAD | `/api/v1/users/search` | [UserController@searchUser](../../Modules/User/Http/Controllers/Api/UserController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |

## API / UserPreferences

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| PUT | `/api/v1/user-preferences` | [UserPreferencesController@update](../../Modules/UserPreferences/Http/Controllers/Api/UserPreferencesController.php) | `api`, `Authenticate:sanctum`, `SetLocale` |

## Framework e pacotes

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/sanctum/csrf-cookie` | CsrfCookieController@show | `web` |
| GET, HEAD | `/storage/{path}` | Closure | — |
| PUT | `/storage/{path}` | Closure | — |
| POST | `/telescope/telescope-api/batches` | QueueBatchesController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/batches/{telescopeEntryId}` | QueueBatchesController@show | `telescope` |
| POST | `/telescope/telescope-api/cache` | CacheController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/cache/{telescopeEntryId}` | CacheController@show | `telescope` |
| POST | `/telescope/telescope-api/client-requests` | ClientRequestController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/client-requests/{telescopeEntryId}` | ClientRequestController@show | `telescope` |
| POST | `/telescope/telescope-api/commands` | CommandsController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/commands/{telescopeEntryId}` | CommandsController@show | `telescope` |
| POST | `/telescope/telescope-api/dumps` | DumpController@index | `telescope` |
| DELETE | `/telescope/telescope-api/entries` | EntriesController@destroy | `telescope` |
| POST | `/telescope/telescope-api/events` | EventsController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/events/{telescopeEntryId}` | EventsController@show | `telescope` |
| POST | `/telescope/telescope-api/exceptions` | ExceptionController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/exceptions/{telescopeEntryId}` | ExceptionController@show | `telescope` |
| PUT | `/telescope/telescope-api/exceptions/{telescopeEntryId}` | ExceptionController@update | `telescope` |
| POST | `/telescope/telescope-api/gates` | GatesController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/gates/{telescopeEntryId}` | GatesController@show | `telescope` |
| POST | `/telescope/telescope-api/jobs` | QueueController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/jobs/{telescopeEntryId}` | QueueController@show | `telescope` |
| POST | `/telescope/telescope-api/logs` | LogController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/logs/{telescopeEntryId}` | LogController@show | `telescope` |
| POST | `/telescope/telescope-api/mail` | MailController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/mail/{telescopeEntryId}` | MailController@show | `telescope` |
| GET, HEAD | `/telescope/telescope-api/mail/{telescopeEntryId}/download` | MailEmlController@show | `telescope` |
| GET, HEAD | `/telescope/telescope-api/mail/{telescopeEntryId}/preview` | MailHtmlController@show | `telescope` |
| POST | `/telescope/telescope-api/models` | ModelsController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/models/{telescopeEntryId}` | ModelsController@show | `telescope` |
| GET, HEAD | `/telescope/telescope-api/monitored-tags` | MonitoredTagController@index | `telescope` |
| POST | `/telescope/telescope-api/monitored-tags` | MonitoredTagController@store | `telescope` |
| POST | `/telescope/telescope-api/monitored-tags/delete` | MonitoredTagController@destroy | `telescope` |
| POST | `/telescope/telescope-api/notifications` | NotificationsController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/notifications/{telescopeEntryId}` | NotificationsController@show | `telescope` |
| POST | `/telescope/telescope-api/queries` | QueriesController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/queries/{telescopeEntryId}` | QueriesController@show | `telescope` |
| POST | `/telescope/telescope-api/redis` | RedisController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/redis/{telescopeEntryId}` | RedisController@show | `telescope` |
| POST | `/telescope/telescope-api/requests` | RequestsController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/requests/{telescopeEntryId}` | RequestsController@show | `telescope` |
| POST | `/telescope/telescope-api/schedule` | ScheduleController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/schedule/{telescopeEntryId}` | ScheduleController@show | `telescope` |
| POST | `/telescope/telescope-api/toggle-recording` | RecordingController@toggle | `telescope` |
| POST | `/telescope/telescope-api/views` | ViewsController@index | `telescope` |
| GET, HEAD | `/telescope/telescope-api/views/{telescopeEntryId}` | ViewsController@show | `telescope` |
| GET, HEAD | `/telescope/{view?}` | HomeController@index | `telescope` |
| GET, HEAD | `/up` | Closure | — |

## Web / Aplicação

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/admin` | [HomeController@index](../../app/Http/Controllers/Backend/HomeController.php) | `web`, `CheckAdmin`, `Authenticate` |
| GET, HEAD | `/confirm-password` | [ConfirmablePasswordController@show](../../app/Http/Controllers/Auth/ConfirmablePasswordController.php) | `web`, `Authenticate` |
| POST | `/confirm-password` | [ConfirmablePasswordController@store](../../app/Http/Controllers/Auth/ConfirmablePasswordController.php) | `web`, `Authenticate` |
| POST | `/email/verification-notification` | [EmailVerificationNotificationController@store](../../app/Http/Controllers/Auth/EmailVerificationNotificationController.php) | `web`, `Authenticate`, `ThrottleRequests:6,1` |
| GET, HEAD | `/forgot-password` | [PasswordResetLinkController@create](../../app/Http/Controllers/Auth/PasswordResetLinkController.php) | `web`, `RedirectIfAuthenticated` |
| POST | `/forgot-password` | [PasswordResetLinkController@store](../../app/Http/Controllers/Auth/PasswordResetLinkController.php) | `web`, `RedirectIfAuthenticated` |
| GET, HEAD | `/login` | [AuthenticatedSessionController@create](../../app/Http/Controllers/Auth/AuthenticatedSessionController.php) | `web`, `RedirectIfAuthenticated` |
| POST | `/login` | [AuthenticatedSessionController@store](../../app/Http/Controllers/Auth/AuthenticatedSessionController.php) | `web`, `RedirectIfAuthenticated` |
| POST | `/logout` | [AuthenticatedSessionController@destroy](../../app/Http/Controllers/Auth/AuthenticatedSessionController.php) | `web`, `Authenticate` |
| PUT | `/password` | [PasswordController@update](../../app/Http/Controllers/Auth/PasswordController.php) | `web`, `Authenticate` |
| GET, HEAD | `/register` | [RegisteredUserController@create](../../app/Http/Controllers/Auth/RegisteredUserController.php) | `web`, `RedirectIfAuthenticated` |
| POST | `/register` | [RegisteredUserController@store](../../app/Http/Controllers/Auth/RegisteredUserController.php) | `web`, `RedirectIfAuthenticated` |
| POST | `/reset-password` | [NewPasswordController@store](../../app/Http/Controllers/Auth/NewPasswordController.php) | `web`, `RedirectIfAuthenticated` |
| GET, HEAD | `/reset-password/{token}` | [NewPasswordController@create](../../app/Http/Controllers/Auth/NewPasswordController.php) | `web`, `RedirectIfAuthenticated` |
| GET, HEAD | `/verify-email` | [EmailVerificationPromptController](../../app/Http/Controllers/Auth/EmailVerificationPromptController.php) | `web`, `Authenticate` |
| GET, HEAD | `/verify-email/{id}/{hash}` | [VerifyEmailController](../../app/Http/Controllers/Auth/VerifyEmailController.php) | `web`, `Authenticate`, `ValidateSignature`, `ThrottleRequests:6,1` |

## Web / Asset

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/admin/assets` | [AssetController@index](../../Modules/Asset/Http/Controllers/AssetController.php) | `web`, `Authenticate` |
| POST | `/admin/assets` | [AssetController@store](../../Modules/Asset/Http/Controllers/AssetController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/assets/create` | [AssetController@create](../../Modules/Asset/Http/Controllers/AssetController.php) | `web`, `Authenticate` |
| DELETE | `/admin/assets/{asset}` | [AssetController@destroy](../../Modules/Asset/Http/Controllers/AssetController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/assets/{asset}` | [AssetController@show](../../Modules/Asset/Http/Controllers/AssetController.php) | `web`, `Authenticate` |
| PUT, PATCH | `/admin/assets/{asset}` | [AssetController@update](../../Modules/Asset/Http/Controllers/AssetController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/assets/{asset}/edit` | [AssetController@edit](../../Modules/Asset/Http/Controllers/AssetController.php) | `web`, `Authenticate` |

## Web / Category

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/admin/categories` | [CategoryController@index](../../Modules/Category/Http/Controllers/Admin/CategoryController.php) | `web`, `Authenticate` |
| POST | `/admin/categories` | [CategoryController@store](../../Modules/Category/Http/Controllers/Admin/CategoryController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/categories/create` | [CategoryController@create](../../Modules/Category/Http/Controllers/Admin/CategoryController.php) | `web`, `Authenticate` |
| DELETE | `/admin/categories/{category}` | [CategoryController@destroy](../../Modules/Category/Http/Controllers/Admin/CategoryController.php) | `web`, `Authenticate` |
| PUT, PATCH | `/admin/categories/{category}` | [CategoryController@update](../../Modules/Category/Http/Controllers/Admin/CategoryController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/categories/{category}/edit` | [CategoryController@edit](../../Modules/Category/Http/Controllers/Admin/CategoryController.php) | `web`, `Authenticate` |

## Web / Currency

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/admin/currencies` | [CurrencyController@index](../../Modules/Currency/Http/Controllers/CurrencyController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/currencies/create` | [CurrencyController@create](../../Modules/Currency/Http/Controllers/CurrencyController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/currencies/{currency}/edit` | [CurrencyController@edit](../../Modules/Currency/Http/Controllers/CurrencyController.php) | `web`, `Authenticate` |

## Web / EmailLayout

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/emailTypes` | [EmailTypeController@index](../../Modules/EmailLayout/Http/Controllers/EmailTypeController.php) | `web`, `Authenticate` |
| POST | `/emailTypes` | [EmailTypeController@store](../../Modules/EmailLayout/Http/Controllers/EmailTypeController.php) | `web`, `Authenticate` |
| GET, HEAD | `/emailTypes/create` | [EmailTypeController@create](../../Modules/EmailLayout/Http/Controllers/EmailTypeController.php) | `web`, `Authenticate` |
| DELETE | `/emailTypes/{emailType}` | [EmailTypeController@destroy](../../Modules/EmailLayout/Http/Controllers/EmailTypeController.php) | `web`, `Authenticate` |
| PUT, PATCH | `/emailTypes/{emailType}` | [EmailTypeController@update](../../Modules/EmailLayout/Http/Controllers/EmailTypeController.php) | `web`, `Authenticate` |
| GET, HEAD | `/emailTypes/{emailType}/edit` | [EmailTypeController@edit](../../Modules/EmailLayout/Http/Controllers/EmailTypeController.php) | `web`, `Authenticate` |
| GET, HEAD | `/settings/emailLayouts` | [EmailLayoutController@index](../../Modules/EmailLayout/Http/Controllers/EmailLayoutController.php) | `web`, `Authenticate` |
| GET, HEAD | `/settings/emailLayouts/create/{id}` | [EmailLayoutController@create](../../Modules/EmailLayout/Http/Controllers/EmailLayoutController.php) | `web`, `Authenticate` |
| DELETE | `/settings/emailLayouts/{emailLayout}` | [EmailLayoutController@destroy](../../Modules/EmailLayout/Http/Controllers/EmailLayoutController.php) | `web`, `Authenticate` |
| PUT, PATCH | `/settings/emailLayouts/{emailLayout}` | [EmailLayoutController@update](../../Modules/EmailLayout/Http/Controllers/EmailLayoutController.php) | `web`, `Authenticate` |
| GET, HEAD | `/settings/emailLayouts/{emailLayout}/edit` | [EmailLayoutController@edit](../../Modules/EmailLayout/Http/Controllers/EmailLayoutController.php) | `web`, `Authenticate` |

## Web / Language

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/admin/languages` | [LanguageController@index](../../Modules/Language/Http/Controllers/LanguageController.php) | `web`, `Authenticate` |
| POST | `/admin/languages` | [LanguageController@store](../../Modules/Language/Http/Controllers/LanguageController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/languages/create` | [LanguageController@create](../../Modules/Language/Http/Controllers/LanguageController.php) | `web`, `Authenticate` |
| DELETE | `/admin/languages/{language}` | [LanguageController@destroy](../../Modules/Language/Http/Controllers/LanguageController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/languages/{language}` | [LanguageController@show](../../Modules/Language/Http/Controllers/LanguageController.php) | `web`, `Authenticate` |
| PUT, PATCH | `/admin/languages/{language}` | [LanguageController@update](../../Modules/Language/Http/Controllers/LanguageController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/languages/{language}/edit` | [LanguageController@edit](../../Modules/Language/Http/Controllers/LanguageController.php) | `web`, `Authenticate` |

## Web / Notification

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/admin/notification-keywords` | [NotificationKeywordController@index](../../Modules/Notification/Http/Controllers/NotificationKeywordController.php) | `web`, `Authenticate`, `CheckAdmin` |
| POST | `/admin/notification-keywords` | [NotificationKeywordController@store](../../Modules/Notification/Http/Controllers/NotificationKeywordController.php) | `web`, `Authenticate`, `CheckAdmin` |
| GET, HEAD | `/admin/notification-keywords/create` | [NotificationKeywordController@create](../../Modules/Notification/Http/Controllers/NotificationKeywordController.php) | `web`, `Authenticate`, `CheckAdmin` |
| DELETE | `/admin/notification-keywords/{notification_keyword}` | [NotificationKeywordController@destroy](../../Modules/Notification/Http/Controllers/NotificationKeywordController.php) | `web`, `Authenticate`, `CheckAdmin` |
| PUT, PATCH | `/admin/notification-keywords/{notification_keyword}` | [NotificationKeywordController@update](../../Modules/Notification/Http/Controllers/NotificationKeywordController.php) | `web`, `Authenticate`, `CheckAdmin` |
| GET, HEAD | `/admin/notification-keywords/{notification_keyword}/edit` | [NotificationKeywordController@edit](../../Modules/Notification/Http/Controllers/NotificationKeywordController.php) | `web`, `Authenticate`, `CheckAdmin` |
| GET, HEAD | `/admin/notification-types` | [NotificationTypeController@index](../../Modules/Notification/Http/Controllers/NotificationTypeController.php) | `web`, `Authenticate`, `CheckAdmin` |
| POST | `/admin/notification-types` | [NotificationTypeController@store](../../Modules/Notification/Http/Controllers/NotificationTypeController.php) | `web`, `Authenticate`, `CheckAdmin` |
| GET, HEAD | `/admin/notification-types/create` | [NotificationTypeController@create](../../Modules/Notification/Http/Controllers/NotificationTypeController.php) | `web`, `Authenticate`, `CheckAdmin` |
| GET, HEAD | `/admin/notification-types/{id}/manage` | [NotificationTypeController@showManageForm](../../Modules/Notification/Http/Controllers/NotificationTypeController.php) | `web`, `Authenticate`, `CheckAdmin` |
| PUT | `/admin/notification-types/{id}/manage` | [NotificationTypeController@manage](../../Modules/Notification/Http/Controllers/NotificationTypeController.php) | `web`, `Authenticate`, `CheckAdmin` |
| DELETE | `/admin/notification-types/{notification_type}` | [NotificationTypeController@destroy](../../Modules/Notification/Http/Controllers/NotificationTypeController.php) | `web`, `Authenticate`, `CheckAdmin` |
| GET, HEAD | `/admin/notification-types/{notification_type}` | [NotificationTypeController@show](../../Modules/Notification/Http/Controllers/NotificationTypeController.php) | `web`, `Authenticate`, `CheckAdmin` |
| PUT, PATCH | `/admin/notification-types/{notification_type}` | [NotificationTypeController@update](../../Modules/Notification/Http/Controllers/NotificationTypeController.php) | `web`, `Authenticate`, `CheckAdmin` |
| GET, HEAD | `/admin/notification-types/{notification_type}/edit` | [NotificationTypeController@edit](../../Modules/Notification/Http/Controllers/NotificationTypeController.php) | `web`, `Authenticate`, `CheckAdmin` |
| GET, HEAD | `/admin/notifications` | [BroadcastNotificationController@index](../../Modules/Notification/Http/Controllers/BroadcastNotificationController.php) | `web`, `Authenticate`, `CheckAdmin` |
| POST | `/admin/notifications` | [BroadcastNotificationController@store](../../Modules/Notification/Http/Controllers/BroadcastNotificationController.php) | `web`, `Authenticate`, `CheckAdmin` |
| GET, HEAD | `/admin/notifications/create` | [BroadcastNotificationController@create](../../Modules/Notification/Http/Controllers/BroadcastNotificationController.php) | `web`, `Authenticate`, `CheckAdmin` |
| DELETE | `/admin/notifications/{notification}` | [BroadcastNotificationController@destroy](../../Modules/Notification/Http/Controllers/BroadcastNotificationController.php) | `web`, `Authenticate`, `CheckAdmin` |
| GET, HEAD | `/admin/notifications/{notification}` | [BroadcastNotificationController@show](../../Modules/Notification/Http/Controllers/BroadcastNotificationController.php) | `web`, `Authenticate`, `CheckAdmin` |
| PUT, PATCH | `/admin/notifications/{notification}` | [BroadcastNotificationController@update](../../Modules/Notification/Http/Controllers/BroadcastNotificationController.php) | `web`, `Authenticate`, `CheckAdmin` |
| GET, HEAD | `/admin/notifications/{notification}/edit` | [BroadcastNotificationController@edit](../../Modules/Notification/Http/Controllers/BroadcastNotificationController.php) | `web`, `Authenticate`, `CheckAdmin` |

## Web / Permission

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/admin/permissions` | [PermissionController@index](../../Modules/Permission/Http/Controllers/PermissionController.php) | `web`, `Authenticate` |
| POST | `/admin/permissions` | [PermissionController@store](../../Modules/Permission/Http/Controllers/PermissionController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/permissions/create` | [PermissionController@create](../../Modules/Permission/Http/Controllers/PermissionController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/permissions/data` | [PermissionController@dataTable](../../Modules/Permission/Http/Controllers/PermissionController.php) | `web`, `Authenticate` |
| DELETE | `/admin/permissions/{permission}` | [PermissionController@destroy](../../Modules/Permission/Http/Controllers/PermissionController.php) | `web`, `Authenticate` |
| PUT, PATCH | `/admin/permissions/{permission}` | [PermissionController@update](../../Modules/Permission/Http/Controllers/PermissionController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/permissions/{permission}/edit` | [PermissionController@edit](../../Modules/Permission/Http/Controllers/PermissionController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/roles` | [RoleController@index](../../Modules/Permission/Http/Controllers/RoleController.php) | `web`, `Authenticate` |
| POST | `/admin/roles` | [RoleController@store](../../Modules/Permission/Http/Controllers/RoleController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/roles/create` | [RoleController@create](../../Modules/Permission/Http/Controllers/RoleController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/roles/manage/{id}` | [RoleController@showManageForm](../../Modules/Permission/Http/Controllers/RoleController.php) | `web`, `Authenticate` |
| POST | `/admin/roles/manage/{id}` | [RoleController@manage](../../Modules/Permission/Http/Controllers/RoleController.php) | `web`, `Authenticate` |
| DELETE | `/admin/roles/{role}` | [RoleController@destroy](../../Modules/Permission/Http/Controllers/RoleController.php) | `web`, `Authenticate` |
| PUT, PATCH | `/admin/roles/{role}` | [RoleController@update](../../Modules/Permission/Http/Controllers/RoleController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/roles/{role}/edit` | [RoleController@edit](../../Modules/Permission/Http/Controllers/RoleController.php) | `web`, `Authenticate` |

## Web / SharedRoles

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/admin/shared-permissions` | [SharedPermissionController@index](../../Modules/SharedRoles/Http/Controllers/SharedPermissionController.php) | `web`, `Authenticate`, `SetLocale` |
| POST | `/admin/shared-permissions` | [SharedPermissionController@store](../../Modules/SharedRoles/Http/Controllers/SharedPermissionController.php) | `web`, `Authenticate`, `SetLocale` |
| GET, HEAD | `/admin/shared-permissions/create` | [SharedPermissionController@create](../../Modules/SharedRoles/Http/Controllers/SharedPermissionController.php) | `web`, `Authenticate`, `SetLocale` |
| DELETE | `/admin/shared-permissions/{shared_permission}` | [SharedPermissionController@destroy](../../Modules/SharedRoles/Http/Controllers/SharedPermissionController.php) | `web`, `Authenticate`, `SetLocale` |
| PUT, PATCH | `/admin/shared-permissions/{shared_permission}` | [SharedPermissionController@update](../../Modules/SharedRoles/Http/Controllers/SharedPermissionController.php) | `web`, `Authenticate`, `SetLocale` |
| GET, HEAD | `/admin/shared-permissions/{shared_permission}/edit` | [SharedPermissionController@edit](../../Modules/SharedRoles/Http/Controllers/SharedPermissionController.php) | `web`, `Authenticate`, `SetLocale` |
| GET, HEAD | `/admin/shared-roles` | [SharedRoleController@index](../../Modules/SharedRoles/Http/Controllers/SharedRoleController.php) | `web`, `Authenticate`, `SetLocale` |
| POST | `/admin/shared-roles` | [SharedRoleController@store](../../Modules/SharedRoles/Http/Controllers/SharedRoleController.php) | `web`, `Authenticate`, `SetLocale` |
| GET, HEAD | `/admin/shared-roles/create` | [SharedRoleController@create](../../Modules/SharedRoles/Http/Controllers/SharedRoleController.php) | `web`, `Authenticate`, `SetLocale` |
| GET, HEAD | `/admin/shared-roles/{id}/manage` | [SharedRoleController@showManageForm](../../Modules/SharedRoles/Http/Controllers/SharedRoleController.php) | `web`, `Authenticate`, `SetLocale` |
| PUT | `/admin/shared-roles/{id}/manage` | [SharedRoleController@manage](../../Modules/SharedRoles/Http/Controllers/SharedRoleController.php) | `web`, `Authenticate`, `SetLocale` |
| DELETE | `/admin/shared-roles/{shared_role}` | [SharedRoleController@destroy](../../Modules/SharedRoles/Http/Controllers/SharedRoleController.php) | `web`, `Authenticate`, `SetLocale` |
| PUT, PATCH | `/admin/shared-roles/{shared_role}` | [SharedRoleController@update](../../Modules/SharedRoles/Http/Controllers/SharedRoleController.php) | `web`, `Authenticate`, `SetLocale` |
| GET, HEAD | `/admin/shared-roles/{shared_role}/edit` | [SharedRoleController@edit](../../Modules/SharedRoles/Http/Controllers/SharedRoleController.php) | `web`, `Authenticate`, `SetLocale` |

## Web / User

| Método | Caminho | Handler | Middleware |
|---|---|---|---|
| GET, HEAD | `/admin/users` | [UserController@index](../../Modules/User/Http/Controllers/UserController.php) | `web`, `Authenticate` |
| POST | `/admin/users` | [UserController@store](../../Modules/User/Http/Controllers/UserController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/users/create` | [UserController@create](../../Modules/User/Http/Controllers/UserController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/users/manage/{id}` | [UserController@showManageForm](../../Modules/User/Http/Controllers/UserController.php) | `web`, `Authenticate` |
| POST | `/admin/users/manage/{id}` | [UserController@manage](../../Modules/User/Http/Controllers/UserController.php) | `web`, `Authenticate` |
| DELETE | `/admin/users/{user}` | [UserController@destroy](../../Modules/User/Http/Controllers/UserController.php) | `web`, `Authenticate` |
| PUT, PATCH | `/admin/users/{user}` | [UserController@update](../../Modules/User/Http/Controllers/UserController.php) | `web`, `Authenticate` |
| GET, HEAD | `/admin/users/{user}/edit` | [UserController@edit](../../Modules/User/Http/Controllers/UserController.php) | `web`, `Authenticate` |

