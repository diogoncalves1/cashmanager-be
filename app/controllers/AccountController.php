<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\models\Account;
use app\models\Database;
use app\Models\User;
use app\Models\AccountUser;
use app\Models\ShareRequest;
use app\Models\Transaction;
use DateTime;

require "../app/core/functions.php";

class AccountController
{
    private $conn;
    private $user;
    private $accountModel;

    function __construct()
    {
        $this->conn = Database::getConn();
        $this->user = new User();
        $this->accountModel = new Account();
    }

    function index()
    {
        $accounts = $this->accountModel->getUserAccounts($this->user->id,);
        return Controller::view("accounts/manage-accounts", ['userAccounts' => $accounts]);
    }

    function add_account()
    {
        return Controller::view("accounts/create-account");
    }

    private function addAccount(string $name, float $intialAmount)
    {
        $createAt = $updateAt = date("Y-m-d H:i:s");
        $conditions = ["name", "create_at", "update_at"];
        $values = [$name, $createAt, $updateAt];
        $accountId = Account::add($conditions, $values);


        $conditions = ["account_id", "user_id", "role_id"];
        $values = [$accountId, $this->user->id, 1];
        AccountUser::add($conditions, $values);

        if ($intialAmount > 0) {
            $date = date("Y-m-d");
            $transactionModel = new Transaction();
            $transactionModel->addTransaction("", $this->user->id, 0, 1, $intialAmount, $accountId, $date, "", "");
            $this->accountModel->addCashAccount($accountId, $intialAmount);
        }
    }

    function store_account($p)
    {
        session_start();
        $this->addAccount($p->name, $p->value);
        if ($_COOKIE['lang'] == "PT")
            $_SESSION["alert"] = "Sua Conta foi criada com sucesso";
        else
            $_SESSION["alert"] = "Your Account has been successfully created";
        header("location: /CashManager/accounts");
    }

    public function deleteShareAcc($p)
    {
        $userId = $p->user_id;
        $accountId = $p->acc_id;
        $accountUserInstance = new AccountUser();
        $roleId = $accountUserInstance->getUserRole($accountId, $userId);
        if (hasPerm($this->conn, $roleId, "edit_user_cash")) {
            $cashAccount = $this->accountModel->getCashAccount($accountId);
            $userInstance = new User();
            $userInstance->withdrawCash($userId, $cashAccount);
        }

        $accountUserInstance->removeUserAccount($accountId, $userId);
    }

    public function edit_account()
    {
        $accountId = $_GET["i"];
        $userId = $this->user->id;
        $accountUser = $this->accountModel->getAccount($accountId, $userId);
        return Controller::view("accounts/edit", ["accountUser" => $accountUser]);
    }

    public function update_account($p)
    {
        $account_id = $_GET['i'];
        $this->accountModel->updateAccount($account_id, $p->name);
        header("location: /CashManager/accounts");
    }

    public function delete()
    {
        $accountId = $_GET["i"];
        $userId = $this->user->id;
        $account = $this->accountModel->getAccount($accountId, $userId);

        if ($account) {
            $permission = "edit_user_cash";
            $cashAccount = $this->accountModel->getCashAccount($accountId);
            $userInstance = new User();
            $users = $userInstance->getUsersCashByAccountPermission($accountId, $permission);
            if ($users)
                foreach ($users as $user) {
                    $userCash = $user['cash'] - $cashAccount;
                    $userInstance->updateCash($user["id"], $userCash);
                }

            $accountUserInstance = new AccountUser();
            $accountUserInstance->removeAllAccount($accountId);
            $transactionInstance = new Transaction();
            $transactionInstance->deleteAccountTransactions($accountId);
            $shareRequestInstance = new ShareRequest();
            $shareRequestInstance->deleteSharesRequests(1, $accountId);

            $this->accountModel->deleteAccount($accountId);
            echo 1;
        } else {
            echo null;
        }
    }

    public function comparison()
    {
        $userAccounts = $this->accountModel->getUserAccounts($this->user->id);
        $dateComparison = [];
        $accountsMonthsAmount = [];
        $accountNumber = 0;
        if ($userAccounts) {
            foreach ($userAccounts as $account) {
                $accountsName[] = $account["name"];
                $transactions = $this->accountModel->getAccountTransactions($account["id"]);
                $lastDate = date("Y-m");
                if ($transactions) {
                    foreach ($transactions as $transaction) {
                        $transactionDate = new DateTime($transaction["date"]);
                        $transactionDate = $transactionDate->format("Y-m");

                        if ($lastDate != $transactionDate)
                            if (!in_array($transactionDate, $dateComparison))
                                $dateComparison[] = $transactionDate;

                        if ($transaction["type"]) {
                            if (!isset($accountsMonthsAmount[$transactionDate][$accountNumber]))
                                $accountsMonthsAmount[$transactionDate][$accountNumber] = $transaction["value"];
                            else
                                $accountsMonthsAmount[$transactionDate][$accountNumber] += $transaction["value"];
                        } else {
                            if (!isset($accountsMonthsAmount[$transactionDate][$accountNumber]))
                                $accountsMonthsAmount[$transactionDate][$accountNumber] = -$transaction["value"];
                            else
                                $accountsMonthsAmount[$transactionDate][$accountNumber] -= $transaction["value"];
                        }

                        $lastDate = new DateTime($transaction["date"]);
                        $lastDate = $lastDate->format("Y-m");
                    }
                    if (!in_array($lastDate, $dateComparison))
                        $dateComparison[] = $lastDate;
                }
                $accountNumber++;
            }
        }

        $data = ["dateComparison" => $dateComparison, "accountsMonthsAmount" => $accountsMonthsAmount, "accountsName" => $accountsName];

        Controller::View("accounts/comparison-account", $data);
    }
}