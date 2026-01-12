<?php
namespace Modules\FinancialGoal\Exceptions\FinancialGoalTransactions;

use Exception;

class UnauthorizedViewFinancialGoalTransactionException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        $this->message = __('financialgoal::exceptions.financial-goal-transactions.unauthorizedViewFinancialGoalTransactionException');
    }
}
