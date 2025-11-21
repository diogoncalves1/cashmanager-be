<?php
namespace Modules\Accounts\Exceptions\Accounts;

use Exception;

class AccountNotFoundException extends Exception
{
    protected $message;
    protected $code = 404;

    public function __construct()
    {
        parent::__construct(__('accounts::exceptions.accounts.accountNotFoundException'), $this->code);
    }
}
