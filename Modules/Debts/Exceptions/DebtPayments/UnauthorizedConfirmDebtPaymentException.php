<?php
namespace Modules\Debts\Exceptions\DebtPayments;

use Exception;

class UnauthorizedConfirmDebtPaymentException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debt-payments.unauthorizedConfirmDebtPaymentException'), $this->code);
    }
}
