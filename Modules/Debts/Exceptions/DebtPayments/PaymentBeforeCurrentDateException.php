<?php
namespace Modules\Debts\Exceptions\DebtPayments;

use Exception;

class PaymentBeforeCurrentDateException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debt-payments.paymentBeforeCurrentDateException'), $this->code);
    }
}
