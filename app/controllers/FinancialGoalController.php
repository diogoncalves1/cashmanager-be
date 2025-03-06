<?Php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\FinancialGoalCategory;
use app\Models\Priority;
use app\Models\StatusGoal;
use PDO;

class FinancialGoalController
{
    function index()
    {
        Controller::view("financial-goals/manage-financial-goals");
    }
    function add()
    {
        $statusGoalStmt = StatusGoal::all();
        $financialGoalCategoriesStmt = FinancialGoalCategory::all();
        $prioritiesStmt = Priority::all();
        $statusGoal = $statusGoalStmt->fetchAll(PDO::FETCH_ASSOC);
        $financialGoalCategories = $financialGoalCategoriesStmt->fetchAll(PDO::FETCH_ASSOC);
        $priorities = $prioritiesStmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            "financialGoalCategories" => $financialGoalCategories,
            "priorities" => $priorities,
            "statusGoals" => $statusGoal
        ];
        Controller::view("financial-goals/add-financial-goal", $data);
    }
    function store($p)
    {
        session_start();
        require "../backend/querys.php";
        insert_financial_goal($conn, $user_id, $p->value, $p->name, $p->category, $p->start_date, $p->final_date, $p->status, $p->priority);
        if ($_COOKIE['lang'] == "PT")
            $_SESSION['alert'] = "Meta Financeira adicionada com sucesso";
        else
            $_SESSION['alert'] = "Financial Goal added with success";
        header("location: /CashManager/public/financial-goals");
    }
    function edit()
    {
        $statusGoalStmt = StatusGoal::all();
        $financialGoalCategorysStmt = FinancialGoalCategory::all();
        $prioritiesStmt = Priority::all();
        $statusGoal = $statusGoalStmt->fetchAll(PDO::FETCH_ASSOC);
        $financialGoalCategorys = $financialGoalCategorysStmt->fetchAll(PDO::FETCH_ASSOC);
        $priorities = $prioritiesStmt->fetchAll(PDO::FETCH_ASSOC);
        $data = [
            "financialGoalCategorys" => $financialGoalCategorys,
            "priorities" => $priorities,
            "statusGoals" => $statusGoal
        ];
        Controller::view("financial-goals/edit-financial-goal", $data);
    }
    function update($p)
    {
        session_start();
        require "../backend/querys.php";
        $financial_goal_id = $_GET['i'];
        update_financial_goal($conn, $financial_goal_id, $user_id, $p->value, $p->name, $p->start_date, $p->final_date, $p->category, $p->status, $p->priority);
        if ($_COOKIE['lang'] == "PT")
            $_SESSION['alert'] = "Meta Financeira editado com sucesso";
        else
            $_SESSION['alert'] = "Financial Goal updated with success";
        header("location: /CashManager/public/financial-goals");
    }
}