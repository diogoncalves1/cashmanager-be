<?php

namespace Modules\Accounts\Exceptions;

use Exception;

class UnauthorizedCreateTransactionException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('accounts::exceptions.transactions.unauthorizedCreateTransactionException'), $this->code);
    }
}
