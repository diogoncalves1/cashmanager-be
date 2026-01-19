<?php
namespace Modules\Debts\Exceptions\DebtPayments;

use Exception;

class UnauthorizedDeleteDebtPaymentException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debts-payments.unauthorizedDeleteDebtPaymentException'), $this->code);
    }
}
