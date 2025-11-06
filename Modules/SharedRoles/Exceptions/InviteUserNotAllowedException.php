<?php

namespace Modules\SharedRoles\Exceptions;

use Exception;

class InviteUserNotAllowedException extends Exception
{
    protected $message;
    protected $code = 401;

    public function __construct()
    {
        parent::__construct(__('sharedroles::exceptions.shareable.inviteUserNotAllowedException'), $this->code);
    }
}
