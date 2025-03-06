<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\AlertScheduled;
use app\Models\ScheduledExpenses;
use app\Models\Transaction;

class ScheduledExpenseController
{
    private $scheduledExpenseModel;

    public function __construct()
    {
        $this->scheduledExpenseModel = new ScheduledExpenses();
    }

    public function confirm($params)
    {
        $scheduledExpenseId = $params->id;
        $scheduledExpense = $this->scheduledExpenseModel->getScheduledExpense($scheduledExpenseId);

        if ($scheduledExpense) {
            $transactionInstance = new Transaction();
            $alertScheduledInstance = new AlertScheduled();
            $transactionInstance->addTransaction(0, $scheduledExpense["user_id"], $scheduledExpense["cat_id"], 0, $scheduledExpense["value"], $scheduledExpense["account_id"], $scheduledExpense["date"], $scheduledExpense["description"], $scheduledExpense["to_p"]);
            $this->scheduledExpenseModel->delete($scheduledExpenseId);
            $alertScheduledInstance->deleteByScheduledExpenseId($scheduledExpenseId);
        }
    }
}
