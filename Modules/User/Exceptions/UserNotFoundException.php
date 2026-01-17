<?php
namespace Modules\User\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    protected $message;
    protected $code = 404;

    public function __construct()
    {
        parent::__construct(__('user::exceptions.users.userNotFoundException'), $this->code);
    }
}
