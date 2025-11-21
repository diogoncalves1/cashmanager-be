<?php
namespace Modules\Accounts\Exceptions\Transactions;

use Exception;

class UnauthorizedDeletedTransactionException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('accounts::exceptions.transactions.unauthorizedDeletedTransactionException'), $this->code);
    }
}
