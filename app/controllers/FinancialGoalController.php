<?Php

namespace app\Controllers;

use app\Controllers\Controller;

class FinancialGoalController
{
    function index()
    {
        Controller::view("financial-goals/manage-financial-goals");
    }
    function add()
    {
        Controller::view("financial-goals/add-financial-goal");
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
        Controller::view("financial-goals/edit-financial-goal");
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
