<?php
namespace Modules\Debts\Exceptions\DebtPayments;

use Exception;

class DebtPaymentNotFoundException extends Exception
{
    protected $message;
    protected $code = 404;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debts-payments.debtPaymentNotFoundException'), $this->code);
    }
}
