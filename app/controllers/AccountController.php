<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\models\AccountModel;
use app\Models\AuthModel;
use app\Models\AccountUserModel;
use app\Models\RoleModel;
use app\Models\ShareRequest;
use app\Models\UserModel;
use app\Models\TransactionModel;
use DateTime;

class AccountController
{
    private $accountModel;

    function __construct()
    {
        $this->accountModel = new AccountModel();
    }

    // Views
    public function showUserAccounts()
    {
        require "../app/core/functions.php";

        if (AuthModel::checkLogin()) {
            session_start();
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "manage accounts";
            require "../app/core/translate.php";

            $userAccounts = $this->accountModel->getUserAccounts($this->accountModel->id);

            $userCoin = $this->accountModel->coin;

            $data = [
                "userAccounts" => $userAccounts,
                "translate" => $translate,
                "userCoin" => $userCoin
            ];

            Controller::view("accounts/manage-accounts", $data);
        }
    }
    public function showCreateAccountForm()
    {
        require "../app/core/functions.php";

        if (AuthModel::checkLogin()) {
            session_start();
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "create account";
            require "../app/core/translate.php";

            $userCoin = $this->accountModel->coin;

            $data = [
                "translate" => $translate,
                "userCoin" => $userCoin
            ];

            return Controller::view("accounts/create-account", $data);
        }
    }
    public function showEditAccountForm($params)
    {
        require "../app/core/functions.php";

        if (AuthModel::checkLogin()) {
            session_start();
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "edit account";
            require "../app/core/translate.php";

            $accountId = $params->id;

            $accountUserInstance = new AccountUserModel();
            $userRole = $accountUserInstance->getUserRole($accountId, $userId);

            if (hasPerm($userRole, "edit_account")) {
                $userAccount = $this->accountModel->getAccount($accountId);
            }

            $data = [
                "translate" => $translate,
                "userAccount" => $userAccount
            ];

            return Controller::view("accounts/edit-account", $data);
        }
    }
    public function showAccountComparison()
    {
        require("../app/core/functions.php");

        if (AuthModel::checkLogin()) {
            session_start();
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = 'account comparison';
            require "../app/core/translate.php";

            $accountsName = [];
            $userAccounts = $this->accountModel->getUserAccounts($this->accountModel->id);
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

            $data = [
                "dateComparison" => $dateComparison,
                "accountsMonthsAmount" => $accountsMonthsAmount,
                "accountsName" => $accountsName,
                "translate" => $translate
            ];

            return Controller::View("accounts/comparison-account", $data);
        }
    }

    // Backend
    public function addAccount($params)
    {
        require "../app/core/functions.php";

        if (AuthModel::checkLogin()) {
            $accountUserInstance = new AccountUserModel();
            $accountName = $params->name;
            $accountInitialValue = $params->value;
            $userId = $this->accountModel->id;

            $accountId = $this->accountModel->addAccount($accountName);
            if ($accountId) {
                $roleInstance = new RoleModel();
                $roleId = $roleInstance->getRoleIdByName("creator");
                if ($accountUserInstance->addAccountUser($accountId, $userId, $roleId)) {
                    if ($accountInitialValue > 0) {
                        $transactionInstance = new TransactionModel();
                        $dateNow = date("Y-m-d");

                        $transactionInstance->addTransaction(0, $userId, 0, "revenue", $accountInitialValue, $accountId, $dateNow, "", "");
                    }
                    header("location: ../accounts");
                } else {
                    header("location: add");
                }
            } else {
                header("location: add");
            }
        }
    }
    public function updateAccount($params)
    {
        require "../app/core/functions.php";

        if (AuthModel::checkLogin()) {
            $accountId = $params->id;

            $accountUserInstance = new AccountUserModel();
            $userRole = $accountUserInstance->getUserRole($accountId, $this->accountModel->id);

            if (hasPerm($userRole, "edit_account")) {
                $newAccountName = $params->name;

                $this->accountModel->updateAccount($accountId, $newAccountName);
                header("location: ../../accounts");
            } else
                header("location: " . $accountId);
        }
    }
    public function deleteAccount($params)
    {
        require "../app/core/functions.php";

        if (AuthModel::checkLogin()) {
            $accountId = $params->id;
            $userId = $this->accountModel->id;

            $accountUserInstance = new AccountUserModel();
            $userRoleId = $accountUserInstance->getUserRole($accountId, $userId);

            if (hasPerm($userRoleId, "delete_account")) {
                $account = $this->accountModel->getAccount($accountId);

                if ($account) {
                    $shareRequestInstance = new ShareRequest();
                    $userInstance = new UserModel();

                    $permission = "edit_user_cash";
                    $cashAccount = $this->accountModel->getCashAccount($accountId);

                    $users = $userInstance->getUsersCashByAccountPermission($accountId, $permission);

                    if ($users) {
                        foreach ($users as $user) {
                            $userInstance->withdrawUserCash($user["id"], (float) $cashAccount);
                        }
                    }

                    $shareRequestInstance->deleteSharesRequests("account", $accountId);
                    $this->accountModel->deleteAccount($accountId);

                    echo 1;
                }
            } else
                echo 0;
        }
    }


    public function deleteShareAcc($p)
    {
        if (AuthModel::checkLogin()) {
            $userId = $p->user_id;
            $accountId = $p->acc_id;
            $accountUserInstance = new AccountUserModel();
            $roleId = $accountUserInstance->getUserRole($accountId, $userId);
            if (hasPerm($roleId, "edit_user_cash")) {
                $cashAccount = $this->accountModel->getCashAccount($accountId);
                $userInstance = new UserModel();
                $userInstance->withdrawUserCash($userId, $cashAccount);
            }

            $accountUserInstance->removeUserAccount($accountId, $userId);
        }
    }
}