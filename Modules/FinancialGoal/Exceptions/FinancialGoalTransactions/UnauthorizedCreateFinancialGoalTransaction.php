<?php
namespace Modules\FinancialGoal\Exceptions\FinancialGoalTransactions;

use Exception;

class UnauthorizedCreateFinancialGoalTransaction extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        $this->message = __('financialgoal::exceptions.financial-goal-transactions.unauthorizedCreateFinancialGoalTransaction');
    }
}
