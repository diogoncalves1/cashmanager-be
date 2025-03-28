<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\AuthModel;
use app\Models\AccountModel;
use app\Models\UserModel;
use app\Models\TransactionModel;
use DateTime;

class HomeController
{
    public function viewHome()
    {
        require "../app/core/functions.php";

        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            session_start();
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "home";

            $transactionInstance = new TransactionModel();
            $userInstance = new UserModel();

            $numUserTransactions = $transactionInstance->countTransactionsByUser($userInstance->id);

            if ($numUserTransactions > 15) {
                $userTransactions = $transactionInstance->getUserTransactionsWithLimit($userInstance->id, 15, 0);
                $numMaxUserTransactions = 15;
            } else {
                $userTransactions = $transactionInstance->getUserTransactions($userInstance->id);
                $numMaxUserTransactions = $numUserTransactions;
            }

            $userName = $userInstance->name;
            $userCoin = $userInstance->coin;
            $userCash = $userInstance->getUserCash($userInstance->id);

            $thisYear = date("Y-01-01");

            $totalRevenues = $transactionInstance->getTotalUserRevenue($userId);
            $totalExpenses = $transactionInstance->getTotalUserExpense($userId);
            $revenuesThisYear = $transactionInstance->getTotalUserRevenueByData($userId, $thisYear);

            foreach ($userTransactions as $key => $transaction) {
                if ($transaction['type'] == "revenue") {
                    $userTransactions[$key]["color"] = "#195905";
                    $userTransactions[$key]["class"] = "success";
                    if ($_COOKIE['mode'] == "light")
                        $userTransactions[$key]["light"] = "table-success";
                    else
                        $userTransactions[$key]["light"] = "";
                } else {
                    $userTransactions[$key]["color"] = "#e30022;";
                    $userTransactions[$key]["class"] = "danger";
                    if ($_COOKIE['mode'] == "light")
                        $userTransactions[$key]["light"] = "table-danger";
                    else
                        $userTransactions[$key]["light"] = "";
                }
            }

            $data = [
                "translate" => $translate,
                "userTransactions" => $userTransactions,
                "userName" => $userName,
                "numMaxUserTransactions" => $numMaxUserTransactions,
                "totalRevenues" => number_format($totalRevenues, 2),
                "totalExpenses" => number_format($totalExpenses, 2),
                "userCash" => number_format($userCash, 2),
                "revenuesThisYear" => number_format($revenuesThisYear, 2),
                "userCoin" => $userCoin,
            ];

            return Controller::view("home/home", $data);
        }
        header("location: /CashManager/sign-up");
    }
}
