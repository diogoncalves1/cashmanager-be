<?php

function load(string $controller, string $action)
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

        $controllerInstance->$action((object) $_REQUEST);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

$router = [
    "GET" => [
        "/CashManager/home" => fn() => load("HomeController", "index"),
        "/CashManager/monthly-comparison" => fn() => load("MonthlyComparisonController", "index"),
        "/CashManager/year-comparison" => fn() => load("YearComparisonController", "index"),
        "/CashManager/monthly-summary" => fn() => load("MonthlySummaryController", "index"),
        "/CashManager/expenses-summary" => fn() => load("ExpensesSummaryController", "index"),
        "/CashManager/accounts/add" => fn() => load("AccountController", "add_account"),
        "/CashManager/accounts" => fn() => load("AccountController", "index"),
        "/CashManager/accounts/edit" => fn() => load("AccountController", "edit_account"),
        "/CashManager/accounts/comparison" => fn() => load("AccountController", "comparison"),
        "/CashManager/objectives/add" => fn() => load("ObjectiveController", "add_objective"),
        "/CashManager/objectives/invest" => fn() => load("ObjectiveController", "invest"),
        "/CashManager/objectives/view" => fn() => load("ObjectiveController", "view"),
        "/CashManager/objectives/completed" => fn() => load("ObjectiveController", "completed_objectives"),
        "/CashManager/objectives" => fn() => load("ObjectiveController", "index"),
        "/CashManager/objectives/edit" => fn() => load("ObjectiveController", "edit_objective"),
        "/CashManager/transactions/add/expense" => fn() => load("TransactionController", "expense"),
        "/CashManager/transactions/add/revenue" => fn() => load("TransactionController", "revenue"),
        "/CashManager/transactions/view" => fn() => load("TransactionController", "view"),
        "/CashManager/transactions" => fn() => load("TransactionController", "index"),
        "/CashManager/transactions/edit" => fn() => load("TransactionController", "edit"),
        "/CashManager/scheduled-expenses/add" => fn() => load("TransactionController", "schedule_expense"),
        "/CashManager/scheduled-expenses" => fn() => load("TransactionController", "scheduled_expenses"),
        "/CashManager/scheduled-expenses/edit" => fn() => load("TransactionController", "update_scheduled_expense"),
        "/CashManager/scheduled-expenses/view" => fn() => load("TransactionController", "view_scheduled_expense"),
        "/CashManager/debts" => fn() => load("DebtController", "index"),
        "/CashManager/debts/add" => fn() => load("DebtController", "add"),
        "/CashManager/debts/edit" => fn() => load("DebtController", "edit"),
        "/CashManager/loans" => fn() => load("LoanController", "index"),
        "/CashManager/loans/add" => fn() => load("LoanController", "add"),
        "/CashManager/budgets/add" => fn() => load("BudgetController", "add"),
        "/CashManager/budgets" => fn() => load("BudgetController", "index"),
        "/CashManager/budgets/edit" => fn() => load("BudgetController", "edit"),
        "/CashManager/financial-goals/add" => fn() => load("FinancialGoalController", "add"),
        "/CashManager/financial-goals" => fn() => load("FinancialGoalController", "index"),
        "/CashManager/financial-goals/edit" => fn() => load("FinancialGoalController", "edit"),
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
        "/CashManager/sign-up" => fn() => load("UserController", "sing_up"),
        "/CashManager/login" => fn() => load("UserController", "login"),
        "/CashManager/logout" => fn() => load("UserController", "logout"),
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
        "/CashManager/accounts/add" => fn() => load("AccountController", "store_account"),
        "/CashManager/accounts/delete-share" => fn() => load("AccountController", "deleteShareAcc"),
        "/CashManager/accounts/edit" => fn() => load("AccountController", "update_account"),
        "/CashManager/objectives/add" => fn() => load("ObjectiveController", "store_objective"),
        "/CashManager/objectives/invest" => fn() => load("ObjectiveController", "invest_objective"),
        "/CashManager/objectives/edit" => fn() => load("ObjectiveController", "update_objective"),
        "/CashManager/transactions/add/expense" => fn() => load("TransactionController", "store_expense"),
        "/CashManager/transactions/add/revenue" => fn() => load("TransactionController", "store_revenue"),
        "/CashManager/transactions/edit" => fn() => load("TransactionController", "update"),
        "/CashManager/transactions/delete" => fn() => load("TransactionController", "delete"),
        "/CashManager/scheduled-expenses/add" => fn() => load("TransactionController", "store_schedule_expense"),
        "/CashManager/scheduled-expenses/edit" => fn() => load("TransactionController", "update_scheduled_expense"),
        "/CashManager/scheduled-expenses/confirm" => fn() => load("ScheduledExpenseController", "confirm"),
        "/CashManager/debts/add" => fn() => load("DebtController", "store"),
        "/CashManager/debts/edit" => fn() => load("DebtController", "update"),
        "/CashManager/budgets/add" => fn() => load("BudgetController", "store"),
        "/CashManager/budgets/edit" => fn() => load("BudgetController", "update"),
        "/CashManager/financial-goals/add" => fn() => load("FinancialGoalController", "store"),
        "/CashManager/financial-goals/edit" => fn() => load("FinancialGoalController", "update"),
        "/CashManager/share" => fn() => load("ShareController", "store"),
        "/CashManager/settings" => fn() => load("SettingsController", "update"),
        "/CashManager/sign-up" => fn() => load("UserController", "test_sing_up"),
        "/CashManager/login" => fn() => load("UserController", "test_login"),
        "/CashManager/admin/categories-expenses/add" => fn() => load("CategoryController", "store"),
        "/CashManager/admin/categories-expenses/edit" => fn() => load("CategoryController", "update"),
        "/CashManager/admin/categories-expenses/delete" => fn() => load("CategoryController", "delete"),
    ],
    "DELETE" => [
        "/CashManager/accounts/delete" => fn() => load("AccountController", "delete"),
    ]
];
