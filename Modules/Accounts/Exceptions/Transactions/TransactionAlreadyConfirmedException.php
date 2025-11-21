<?php
namespace Modules\Accounts\Exceptions\Transactions;

use Exception;

class TransactionAlreadyConfirmedException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('accounts::exceptions.transactions.transactionAlreadyConfirmedException'), $this->code);
    }
}
