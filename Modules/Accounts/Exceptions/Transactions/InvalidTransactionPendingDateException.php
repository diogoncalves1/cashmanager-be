<?php
namespace Modules\Accounts\Exceptions\Transactions;

use Exception;

class InvalidTransactionPendingDateException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('accounts::exceptions.transactions.invalidTransactionPendingDateException'), $this->code);
    }
}
