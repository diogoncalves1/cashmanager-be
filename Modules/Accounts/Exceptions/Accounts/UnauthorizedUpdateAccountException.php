<?php
namespace Modules\Accounts\Exceptions\Accounts;

use Exception;

class UnauthorizedUpdateAccountException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('accounts::exceptions.accounts.unauthorizedUpdateAccountException'), $this->code);
    }
}
