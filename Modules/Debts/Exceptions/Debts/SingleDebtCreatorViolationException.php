<?php
namespace Modules\Debts\Exceptions\Debts;

use Exception;

class SingleDebtCreatorViolationException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debts.singleDebtCreatorViolationException'), $this->code);
    }
}
