<?php

namespace Modules\Friends\Exceptions;

use Exception;

class AlreadyFriendsException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('friends::exceptions.friendships.alreadyFriendsException'), $this->code);
    }
}
