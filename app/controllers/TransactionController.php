<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\Account;
use app\models\Database;
use app\models\User;
use app\Models\Transaction;
use app\Models\Alert;

require "../app/core/functions.php";
class TransactionController
{
    private $conn;
    private $user;
    private $transactionModel;
    public function __construct()
    {
        $this->conn = new Database();
        $this->user = new User();
        $this->transactionModel = new Transaction();
    }

    function index()
    {
        Controller::view("transactions/manage-transactions");
    }
    function expense()
    {
        $curDate = date("Y-m-d");
        $maxAcc = 0;
        $accountInstance = new Account($this->conn->conn);
        $accounts = $accountInstance->getUserAccounts($this->user->id);
        foreach ($accounts as $account) {
            if (hasPerm($this->conn->conn, $account["role_id"], "add_transactions")) {
                $maxAcc = $account['cash'];
                break;
            }
        }

        Controller::view("transactions/expense", ["curDate" => $curDate, "maxAcc" => $maxAcc, "accountsUser" => $accounts]);
    }
    function store_expense($params)
    {
        session_start();
        $this->transactionModel->addTransaction($_FILES["proof"], $this->user->id, $params->category, 0, $params->value, $params->account, $params->date, $params->description, $params->to);
        if ($_COOKIE['lang'] == "PT")
            $_SESSION['alert'] = "Nova Despesa adicionada com sucesso";
        else
            $_SESSION['alert'] = "New Expense Successfully Added";
        header("location: /CashManager/transactions");
    }
    function revenue()
    {
        $curDate = date("Y-m-d");
        $accountsInstance = new Account($this->conn->conn);
        $accountsUser = $accountsInstance->getUserAccounts($this->user->id);
        Controller::view("transactions/revenue", ["curDate" => $curDate, "accountsUser" => $accountsUser]);
    }
    function store_revenue($params)
    {
        session_start();
        $this->transactionModel->addTransaction(0, $this->user->id, 0, 1, $params->value, $params->account, $params->date, $params->description, $params->to);
        if ($_COOKIE['lang'] == "PT")
            $_SESSION['alert'] = "Nova Receita adicionada com sucesso";
        else
            $_SESSION['alert'] = "New Revenue Successfully Added";
        header("location: /CashManager/transactions");
    }
    function edit()
    {
        Controller::view("transactions/edit-transaction");
    }
    function update($params)
    {
        session_start();
        require "../backend/querys.php";
        $transactionId = $_GET["i"];

        $newValue = $params->value;
        $transaction = $this->transactionModel->getTransaction($transactionId);
        if ($transaction) {

            $accountId = $transaction["account_id"];
            $initalValue = $transaction["value"];
            $accountInstance = new Account();

            $users = $this->user->getUsersCashByAccountPermission($accountId, "edit_user_cash");
            if ($users) {
                foreach ($users as $user) {
                    if ($transaction['type'] == 1)
                        $this->user->addCash($user["id"], $newValue - $initalValue);
                    else
                        $this->user->withdrawCash($user["id"], $newValue - $initalValue);
                }
            }

            if ($transaction['type'] == 1)
                $accountInstance->addCashAccount($accountId, $newValue - $initalValue);
            else
                $accountInstance->withdrawCashAccount($accountId, $newValue - $initalValue);

            $proof = 0;
            if (isset($_FILES["proof"]))
                $proof = $_FILES["proof"];

            $category = 0;
            if (isset($params->category))
                $category = $params->category;

            $this->transactionModel->updateTransaction($transactionId, $newValue, $params->date, $params->description, $params->to, $category, $proof);

            if ($_COOKIE['lang'] == "PT")
                $_SESSION['alert'] = "Transação editada com sucesso";
            else
                $_SESSION['alert'] = "Transaction edited successfully";
        }
        header("location: /CashManager/transactions");
    }
    function view()
    {
        Controller::view("transactions/view-transaction");
    }
    function schedule_expense()
    {
        $curDate = date("Y-m-d");
        $accountInstance = new Account($this->conn->conn);
        $accounts = $accountInstance->getUserAccounts($this->user->id);
        Controller::view("transactions/schedule-expense", ["accountsUser" => $accounts, "curDate" => $curDate]);
    }
    function store_schedule_expense($params)
    {
        session_start();
        require "../backend/querys.php";
        add_schedule_expense($conn, $user_id, 0, $params->category, $params->account, $params->value, $params->date, $params->description, $params->to);
        if ($_COOKIE['lang'] == "PT")
            $_SESSION['alert'] = "Nova Despesa Agendada adicionada com sucesso";
        else
            $_SESSION['alert'] = "New Scheduled Expense Successfully Added";
        header("location: /CashManager/scheduled-expenses");
    }
    function scheduled_expenses()
    {
        Controller::view("transactions/scheduled-expenses");
    }
    function edit_schedule_expense()
    {
        Controller::view("transactions/edit-scheduled-expense");
    }
    function update_scheduled_expense($params)
    {
        require "../backend/querys.php";
        update_scheduled_expense($conn, $user_id, $expense_id, $params->value, $params->date, $params->description, $params->to, $params->category);
        header("location: ../frontend/scheduled-expenses.php");
    }
    function view_scheduled_expense()
    {
        Controller::view("transactions/view-scheduled-expense");
    }
    function delete($params)
    {
        $response = "";
        $transactionId = $params->id;
        require "../backend/querys.php";
        require "../app/core/translate.php";

        $transaction = $this->transactionModel->getTransaction($transactionId);

        if ($transaction) {
            $userInstance = new User();
            $accountInstance = new Account();
            $accountId = $transaction["account_id"];
            $users = $userInstance->getUsersCashByAccountPermission($accountId, "edit_user_cash");

            if ($users) {
                foreach ($users as $user) {
                    if ($transaction['type'] == 0) {
                        $userInstance->addCash($user["id"], $transaction["value"]);
                        $alertInstance = new Alert();
                        $alertInstance->checkAlert($user["id"], $transaction["cat_id"], $transaction["values"], $transaction["date"], 1);
                    } else
                        $userInstance->withdrawCash($user["id"], $transaction["value"]);
                }
            }

            if ($transaction)
                $accountInstance->withdrawCashAccount($accountId, $transaction["value"]);
            else
                $accountInstance->addCashAccount($accountId, $transaction["value"]);

            $this->transactionModel->deleteTransaction($transactionId);

            if ($transaction['proof'] != "")
                unlink("../assets/images/proofs/" . $transaction['proof']);

            $transactions = $this->transactionModel->getUserVisibleTransactions($this->user->id);

            if ($transactions) {
                $total_transactions_table = [];
                $total_transactions_table[-1] = 0;
                $i = 0;
                foreach ($transactions as $transaction) {
                    if (hasPermission($conn, $transaction['role_id'], "edit_user_cash")) {
                        if ($transaction['type'] == 1)
                            $total_transactions_table[$i] = $total_transactions_table[$i - 1] + $transaction['value'];
                        else
                            $total_transactions_table[$i] = $total_transactions_table[$i - 1] - $transaction['value'];
                    }
                    $i++;
                }

                foreach ($transactions as $transaction) {
                    if ($transaction["type"] == 1) {
                        $color = "#195905";
                    } else
                        $color = "#e30022;";
                    if ($transaction["type"] == 1)
                        $tableColor = "success";
                    else
                        $tableColor = "danger";

                    if ($total_transactions_table[$i - 1] >= 0)
                        $totalColor = "#195905;";
                    else if ($total_transactions_table[$i - 1] < 0)
                        $totalColor = "#e30022;";

                    $tableLight = "";

                    if ($_COOKIE['mode'] == "light") { ?>table-
<?php if ($transaction['type'] == 1)
                            $tableLight = "table-success";
                        else
                            $tableLight = "table-danger";
                    }

                    $response .= '<tr class="' . $tableColor . $tableLight . '">
                                <td style="color: ' . $color . '">' . $transaction["date"] . '</td>
                                <td style="color: ' . $color . '">' . $transaction["value"] . $coin . '</td>
                                <td style="color: ' . $totalColor . '">' . $total_transactions_table[$i - 1] . $coin . '</td>
                                <td style="color: ' . $color . '">
                                    <button  style="background: none; border: none;"
                                    onclick="goToView(' . $transaction['id'] . ')"><svg class="bi"
                                                            style="color: ' . $color . '">
                                                            <use xlink:href="#view" />
                                                        </svg>
                                                    </button>
                                                </td>
                                <td style="color: ' . $color . '"><button style="background: none; border: none;"
                                        onclick="goToEdit(' . $transaction["id"] . ')"><svg class="bi"
                                            style="color: ' . $color . '">
                                            <use xlink:href="#edit" />
                                        </svg></button></td>
                                <td class="center" style="color: ' . $color . '"><button data-bs-toggle="modal" data-bs-target="#delete-modal"
                                        style="background: none; border: none;"
                                        onclick="modal(' . $transaction["id"] . ')"><svg class="bi"
                                            style="color: ' . $color . '">
                                            <use xlink:href="#delete" />
                                        </svg></button></td>
                            </tr>';
                    $i--;
                }
            }


            echo $response;
        }
    }
}
