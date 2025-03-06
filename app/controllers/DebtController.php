<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\Debt;

class DebtController
{
    private $debtModel;

    public function __construct()
    {
        $this->debtModel = new Debt();
    }
    function index()
    {
        Controller::view("debts/manage-debts");
    }
    function add()
    {
        Controller::view("debts/add-debt");
    }
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