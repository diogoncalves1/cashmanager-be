<?php
namespace Modules\Debts\Exceptions\DebtPayments;

use Exception;

class PaymentNotScheduledException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debt-payments.paymentNotScheduledException'), $this->code);
    }
}
