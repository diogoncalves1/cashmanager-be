<?php
namespace Modules\FinancialGoal\Exceptions\FinancialGoal;

use Exception;

class FinancialGoalNotInProgressException extends Exception
{
    protected $code = 500;
    protected $message;

    public function __construct()
    {
        $this->message = __('financialgoal::exceptions.financial-goals.financialGoalNotInProgressException');
    }
}
