<?php

function load(string $controller, string $action, $param = null)
{
    try {
        $controllerNamespace = "app\\controllers\\{$controller}";
        if (!class_exists($controllerNamespace)) {
            throw new Exception("Controller {$controller} not exists");
        }

        $controllerInstance = new $controllerNamespace;

        if (!method_exists($controllerInstance, $action)) {
            throw new Exception("Method {$action} not exists on controller {$controller}");
        }

        if ($param != null)
            $_REQUEST["id"] = $param;

        $controllerInstance->$action((object) $_REQUEST);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

$router = [
    "GET" => [
        "/CashManager/home" => fn() => load("HomeController", "viewHome"),
        "/CashManager/monthly-comparison" => fn() => load("MonthlyComparisonController", "index"),
        "/CashManager/year-comparison" => fn() => load("YearComparisonController", "index"),
        "/CashManager/monthly-summary" => fn() => load("MonthlySummaryController", "index"),
        "/CashManager/expenses-summary" => fn() => load("ExpensesSummaryController", "index"),
        "/CashManager/accounts/add" => fn() => load("AccountController", "showCreateAccountForm"),
        "/CashManager/accounts" => fn() => load("AccountController", "showUserAccounts"),
        "/CashManager/accounts/edit/{id}" => fn($id) => load("AccountController", "showEditAccountForm", $id),
        "/CashManager/accounts/comparison" => fn() => load("AccountController", "showAccountComparison"),
        "/CashManager/objectives/add" => fn() => load("ObjectiveController", "showAddObjectiveForm"),
        "/CashManager/objectives/invest" => fn() => load("ObjectiveController", "showInvestObjectiveForm"),
        "/CashManager/objectives/view" => fn() => load("ObjectiveController", "viewUserObjectives"),
        "/CashManager/objectives/completed" => fn() => load("ObjectiveController", "viewUserCompletedObjectives"),
        "/CashManager/objectives" => fn() => load("ObjectiveController", "showUserObjectivesTable"),
        "/CashManager/objectives/edit/{id}" => fn($id) => load("ObjectiveController", "showEditObjectiveForm", $id),
        "/CashManager/transactions/add/expense" => fn() => load("TransactionController", "showAddExpenseForm"),
        "/CashManager/transactions/add/revenue" => fn() => load("TransactionController", "showAddRevenueForm"),
        "/CashManager/transactions/view/{id}" => fn($id) => load("TransactionController", "viewTransaction", $id),
        "/CashManager/transactions" => fn() => load("TransactionController", "showUserTransactions"),
        "/CashManager/transactions/edit" => fn() => load("TransactionController", "edit"),
        "/CashManager/scheduled-expenses/add" => fn() => load("TransactionController", "schedule_expense"),
        "/CashManager/scheduled-expenses" => fn() => load("TransactionController", "scheduled_expenses"),
        "/CashManager/scheduled-expenses/edit" => fn() => load("TransactionController", "update_scheduled_expense"),
        "/CashManager/scheduled-expenses/view" => fn() => load("TransactionController", "view_scheduled_expense"),
        "/CashManager/debts" => fn() => load("DebtController", "showUserDebts"),
        "/CashManager/debts/add" => fn() => load("DebtController", "showAddDebtForm"),
        "/CashManager/debts/edit" => fn() => load("DebtController", "edit"),
        "/CashManager/loans" => fn() => load("LoanController", "index"),
        "/CashManager/loans/add" => fn() => load("LoanController", "add"),
        "/CashManager/budgets/add" => fn() => load("BudgetController", "add"),
        "/CashManager/budgets" => fn() => load("BudgetController", "index"),
        "/CashManager/budgets/edit" => fn() => load("BudgetController", "edit"),
        "/CashManager/financial-goals/add" => fn() => load("FinancialGoalController", "showAddFinancialGoalForm"),
        "/CashManager/financial-goals" => fn() => load("FinancialGoalController", "showUserFinancialGoals"),
        "/CashManager/financial-goals/edit/{id}" => fn($id) => load("FinancialGoalController", "showEditFinancialGoalForm", $id),
        "/CashManager/share" => fn() => load("ShareController", "index"),
        "/CashManager/shares" => fn() => load("ShareController", "shares"),
        "/CashManager/share/sent" => fn() => load("ShareController", "sent_requests"),
        "/CashManager/shares/edit" => fn() => load("ShareController", "edit"),
        "/CashManager/shares/requests" => fn() => load("ShareController", "requests"),
        "/CashManager/friends" => fn() => load("FriendController", "index"),
        "/CashManager/friends/add" => fn() => load("FriendController", "add"),
        "/CashManager/tools" => fn() => load("SettingsController", "tools"),
        "/CashManager/create-reminder" => fn() => load("SettingsController", "reminder"),
        "/CashManager/settings" => fn() => load("SettingsController", "index"),
        "/CashManager/sign-up" => fn() => load("AuthController", "sing_up"),
        "/CashManager/login" => fn() => load("AuthController", "login"),
        "/CashManager/logout" => fn() => load("AuthController", "logout"),
        "/CashManager/select-lang" => fn() => load("UserController", "select_lang"),
        "/CashManager/admin" => fn() => load("AdminController", "index"),
        "/CashManager/admin/users" => fn() => load("AdminController", "users"),
        "/CashManager/admin/categories-expenses" => fn() => load("CategoryController", "index"),
        "/CashManager/admin/categories-expenses/add" => fn() => load("CategoryController", "add"),
        "/CashManager/admin/categories-expenses/edit" => fn() => load("CategoryController", "edit"),
        "/CashManager/admin/financial-goal-categories" => fn() => load("FinancialGoalCategoryController", "index"),
        "/CashManager/admin/coins" => fn() => load("CoinController", "index"),
        "/CashManager/admin/permissions" => fn() => load("PermissionController", "index"),
        "/CashManager/test" => fn() => load("AdminController", "test"),
    ],
    "POST" => [
        "/CashManager/accounts/add" => fn() => load("AccountController", "addAccount"),
        "/CashManager/accounts/delete-share" => fn() => load("AccountController", "deleteShareAcc"),
        "/CashManager/accounts/edit/{id}" => fn($id) => load("AccountController", "updateAccount", $id),
        "/CashManager/objectives/add" => fn() => load("ObjectiveController", "addObjective"),
        "/CashManager/objectives/invest" => fn() => load("ObjectiveController", "investObjective"),
        "/CashManager/transactions/add/expense" => fn() => load("TransactionController", "addExpense"),
        "/CashManager/transactions/add/revenue" => fn() => load("TransactionController", "addRevenue"),
        "/CashManager/transactions/edit" => fn() => load("TransactionController", "update"),
        "/CashManager/transactions/delete" => fn() => load("TransactionController", "delete"),
        "/CashManager/scheduled-expenses/add" => fn() => load("TransactionController", "store_schedule_expense"),
        "/CashManager/scheduled-expenses/edit" => fn() => load("TransactionController", "update_scheduled_expense"),
        "/CashManager/scheduled-expenses/confirm" => fn() => load("ScheduledExpenseController", "confirm"),
        "/CashManager/debts/add" => fn() => load("DebtController", "store"),
        "/CashManager/debts/edit" => fn() => load("DebtController", "update"),
        "/CashManager/budgets/add" => fn() => load("BudgetController", "store"),
        "/CashManager/budgets/edit" => fn() => load("BudgetController", "update"),
        "/CashManager/financial-goals/add" => fn() => load("FinancialGoalController", "addFinancialGoal"),
        "/CashManager/share" => fn() => load("ShareController", "store"),
        "/CashManager/settings" => fn() => load("SettingsController", "update"),
        "/CashManager/sign-up" => fn() => load("AuthController", "test_sing_up"),
        "/CashManager/login" => fn() => load("AuthController", "test_login"),
        "/CashManager/admin/categories-expenses/add" => fn() => load("CategoryController", "store"),
        "/CashManager/admin/categories-expenses/edit" => fn() => load("CategoryController", "update"),
        "/CashManager/admin/categories-expenses/delete" => fn() => load("CategoryController", "delete")
    ],
    "PUT" => [
        "/CashManager/objectives/edit/{id}" => fn($id) => load("ObjectiveController", "updateObjective", $id),
        "/CashManager/objectives/mark-completed/{id}" => fn($id) => load("ObjectiveController", "markObjectiveCompleted", $id),
        "/CashManager/financial-goals/edit/{id}" => fn($id) => load("FinancialGoalController", "updateFinancialGoal", $id),
    ],
    "DELETE" => [
        "/CashManager/accounts/delete/{id}" => fn($id) => load("AccountController", "deleteAccount", $id),
        "/CashManager/account/remove-user/account/{accountId}/user/{userId}" => fn() => load("AccountController", "deleteShareAcc"),
        "/CashManager/objectives/delete/{id}" => fn($id) => load("ObjectiveController", "deleteObjective", $id),
        "/CashManager/financial-goals/delete/{id}" => fn($id) => load("FinancialGoalController", "deleteFinancialGoal", $id),
    ]
];


$uri = $_SERVER["REQUEST_URI"];
$method = $_SERVER['REQUEST_METHOD'];
function processRequest($method, $uri)
{
    global $router;

    if (isset($router[$method])) {
        foreach ($router[$method] as $route => $action) {
            if (preg_match('#^' . preg_replace('/{[^}]+}/', '([^/]+)', $route) . '$#', $uri, $matches)) {
                array_shift($matches);
                return call_user_func_array($action, $matches);
            } elseif ($route == $uri) {
                return $action();
            }
        }
    }
    return 1;
}
