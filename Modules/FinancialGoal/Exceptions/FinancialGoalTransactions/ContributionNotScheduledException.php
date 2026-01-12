<?php
namespace Modules\FinancialGoal\Exceptions\FinancialGoalTransactions;

use Exception;

class ContributionNotScheduledException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        $this->message = __('financialgoal::exceptions.financial-goal-transactions.contributionNotScheduledException');
    }
}
