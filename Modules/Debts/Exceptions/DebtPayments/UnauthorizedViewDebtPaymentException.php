<?php
namespace Modules\Debts\Exceptions;

use Exception;

class UnauthorizedViewDebtPaymentException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debt-payments.unauthorizedViewDebtPaymentException'), $this->code);
    }
}
