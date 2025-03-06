<?php

namespace app\Controllers;

use app\Models\Account;
use app\Controllers\Controller;
use app\Models\Database;
use app\Models\User;
use app\Models\Transaction;
use app\Models\Objective;
use app\Models\Category;

require "../app/core/functions.php";
class ObjectiveController
{
    private $conn;
    private $user;
    private $objectiveModel;
    function __construct()
    {
        $this->conn = new Database();
        $this->user = new User();
        $this->objectiveModel = new Objective();
    }
    function index()
    {
        Controller::view("objectives/manage-objectives");
    }
    function add_objective()
    {
        Controller::view("objectives/create-objective");
    }
    function invest()
    {
        $accountInstance = new Account($this->conn->conn);
        $max = 0;
        $accountsUser = $accountInstance->getUserAccounts($this->user->id);
        foreach ($accountsUser as $account) {
            if (hasPerm($this->conn->conn, $account["role_id"], "add_transactions")) {
                $max = $account["cash"];
                break;
            }
        }
        $maxGoal = 0;

        $objectivesUser = $this->objectiveModel->getObjectivesUser($this->user->id);
        foreach ($objectivesUser as $objective)
            if (hasPerm($this->conn->conn, $objective["role_id"], "invest_objective")) {
                $maxGoal = $objective['meta'] - $objective['now'];
                break;
            }
        Controller::view("objectives/invest-objective", ["accountsUser" => $accountsUser, "max" => $max, "maxGoal" => $maxGoal, "objectivesUser" => $objectivesUser]);
    }
    function invest_objective($params)
    {
        $this->objectiveModel->invest($params->objective, $params->value);
        $categoryInstance = new Category();
        $category = $categoryInstance->getCategoryIdByCode("investment");
        $date = date("Y-m-d");
        $description = "Invest";
        $to = $this->objectiveModel->getObjectiveName($params->objective);
        $transactionInstance = new Transaction();
        $transactionInstance->addTransaction(0, $this->user->id, $category, 0, $params->value, $params->account, $date, $description, $to);

        session_start();
        require "../backend/querys.php";

        if ($_COOKIE['lang'] == "PT")
            $_SESSION['alert'] = "Investimento feito com sucesso no objetivo " . get_objective_name_by_id($conn, $params->objective);
        else
            $_SESSION['alert'] = "Investment made successfully " . get_objective_name_by_id($conn, $params->objective);
        header("location: /CashManager/public/objectives/view");
    }
    function view()
    {
        Controller::view("objectives/objectives");
    }
    function completed_objectives()
    {
        Controller::view("objectives/completed-objectives");
    }
    function store_objective($params)
    {
        session_start();
        require "../backend/querys.php";
        create_new_objective($conn, $user_id, $params->name, $params->value);
        if ($_COOKIE['lang'] == "PT")
            $_SESSION['alert'] = "Objetivo criado com sucesso";
        else
            $_SESSION['alert'] = "Successfully created goal";
        header("location: /CashManager/public/objectives");
    }
    function edit_objective()
    {
        Controller::view("objectives/edit-objective");
    }
    function update_objective($params)
    {
        require "../backend/querys.php";
        $objetive_id = $_GET['i'];
        update_objective($conn, $objetive_id, $params->name, $params->value);
        header("location: /CashManager/public/objectives");
    }
}
