<?php
namespace Modules\Debts\Exceptions\Debts;

use Exception;

class DebtNotInProgressException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debts.debtNotInProgressException'), $this->code);
    }
}
