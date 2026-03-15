<?php
namespace Modules\Debts\Exceptions\Debts;

use Exception;

class DebtPendingException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debts.debtPendingException'), $this->code);
    }
}
