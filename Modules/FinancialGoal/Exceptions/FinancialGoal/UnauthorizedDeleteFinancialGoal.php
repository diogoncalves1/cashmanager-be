<?php
namespace Modules\FinancialGoal\Exceptions\FinancialGoal;

use Exception;

class UnauthorizedDeleteFinancialGoal extends Exception
{
    protected $code = 403;
    protected $message;

    public function __construct()
    {
        $this->message = __('financialgoal::exceptions.financial-goals.unauthorizedDeleteFinancialGoal');
    }
}
