<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\AuthModel;
use app\Models\DebtModel;

require "../app/core/functions.php";
session_start();

class DebtController
{
    private $debtModel;

    public function __construct()
    {
        $this->debtModel = new DebtModel();
    }

    // Views
    public function showUserDebts()
    {
        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "manage debts";

            $userId = $this->debtModel->id;
            $userCoin = $this->debtModel->coin;
            $userDebts = $this->debtModel->getUserDebts($userId);

            $data = [
                "translate" => $translate,
                "userDebts" => $userDebts,
                "userCoin" => $userCoin,
            ];

            Controller::view("debts/manage-debts", $data);
        }
    }
    public function showAddDebtForm()
    {
        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "add debt";

            $userCoin = $this->debtModel->coin;

            $data = [
                "translate" => $translate,
                "userCoin" => $userCoin,
            ];

            Controller::view("debts/add-debt", $data);
        }
    }















    // Backend









    function store($p)
    {
        session_start();
        require "../backend/querys.php";
        $installment = 0;
        $interest = null;
        $n_inst = null;
        $v_inst = null;
        var_dump($p->rate);
        if ($p->rate != "") {
            $rate = $p->rate;
            if (isset($p->compound_interest))
                $interest = "Compound";
            else
                $interest = "Simple";
        } else
            $rate = null;
        if (isset($p->installment)) {
            $installment = 1;
            $n_inst = $p->n_installments;
            $v_inst = $p->value_installments;
        }

        if ($_COOKIE['lang'] == "PT")
            $_SESSION['alert'] = "Dívida Adicionada com sucesso";
        else
            $_SESSION['alert'] = "Debt Successfully Added";
        insert_debt($conn, $user_id, $p->creditor, $p->total_value, $rate, $installment, $p->due_date, $p->description, $n_inst, $v_inst, $interest);
        header("location: /CashManager/public/debts");
    }
    function edit()
    {
        Controller::view("debts/edit-debt");
    }
    function update($p)
    {
        session_start();
        require "../backend/querys.php";
        $id = $_GET['i'];
        $installment = 0;
        $interest = null;
        $n_inst = null;
        $v_inst = null;
        if ($p->rate != "") {
            $rate = $p->rate;
            if (isset($p->compound_interest))
                $interest = "Compound";
            else
                $interest = "Simple";
        } else
            $rate = null;
        if (isset($p->installment)) {
            $installment = 1;
            $n_inst = $p->n_installments;
            $v_inst = $p->value_installments;
        }
        $this->debtModel->updateDebt();
        update_debt($conn, $id, $p->creditor, $p->total_value, $rate, $installment, $p->due_date, $p->description, $n_inst, $v_inst, $interest);
        if ($_COOKIE['lang'] == "PT")
            $_SESSION['alert'] = "Dívida Atualizada com sucesso";
        else
            $_SESSION['alert'] = "Debt Successfully Updated";
        header("location: /CashManager/public/debts");
    }
}