<?php
namespace Modules\FinancialGoal\Exceptions\FinancialGoalUser;

use Exception;

class SingleFinancialGoalCreatorViolationException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        $this->message = __('financialgoal::exceptions.financial-goal-users.singleFinancialGoalCreatorViolationException');
    }
}
