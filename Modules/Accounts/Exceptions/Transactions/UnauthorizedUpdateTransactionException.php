<?php
namespace Modules\Accounts\Exceptions\Transactions;

use Exception;

class UnauthorizedUpdateTransactionException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('accounts::exceptions.transactions.unauthorizedUpdateTransactionException'), $this->code);
    }
}
