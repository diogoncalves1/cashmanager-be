<?php

namespace Modules\SharedRoles\Exceptions;

use Exception;

class UnauthorizedUpdateUserRoleException extends Exception
{
    protected $message;
    protected $code = 401;

    public function __construct()
    {
        parent::__construct(__('sharedroles::exceptions.shareable.unauthorizedUpdateUserRoleException'), $this->code);
    }
}
