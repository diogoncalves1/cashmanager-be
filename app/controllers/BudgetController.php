<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\TimePeriod;
use PDO;

class BudgetController
{
    function index()
    {
        Controller::view("budgets/manage-budget");
    }
    function add()
    {
        $timePeriodsStmt = TimePeriod::all();
        $timePeriods = $timePeriodsStmt->fetchAll(PDO::FETCH_ASSOC);
        $data = ["timePeriods" => $timePeriods];
        Controller::view("budgets/add-budget", $data);
    }
    function store($params)
    {
        session_start();
        require "../backend/querys.php";
        search_limits($conn, $params->value, $user_id, $params->category, $params->period);
        if ($_COOKIE['lang'] == "PT")
            $_SESSION["alert"] = "Orçamentos Adicionados com Sucesso";
        else
            $_SESSION["alert"] = "Budgets Successfully Added";
        header("location: /CashManager/public/budgets");
    }
    function edit()
    {
        Controller::view("budgets/edit-budget");
    }
    function update($params)
    {
        session_start();
        require "../backend/querys.php";
        $limit_id = $_GET['i'];
        update_limit($conn, $limit_id, $params->max);
        check_alert($conn, $user_id, 0, 0, 0, 0, $limit_id);
        if ($_COOKIE['lang'] == "PT")
            $_SESSION['alert'] = "Orçamento Atualizado com sucesso";
        else
            $_SESSION['alert'] = "Budget Updated with success";
        header("location: /CashManager/public/budgets");
    }
}