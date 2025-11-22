<?php

namespace Modules\Friends\Exceptions;

use Exception;

class UserBlockedException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('friends::exceptions.friendships.userBlockedException'), $this->code);
    }
}
