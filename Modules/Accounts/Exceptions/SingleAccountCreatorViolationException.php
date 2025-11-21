<?php

namespace Modules\Accounts\Exceptions;

use Exception;

class SingleAccountCreatorViolationException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('accounts::exceptions.accounts.singleAccountCreatorViolationException'), $this->code);
    }
}
