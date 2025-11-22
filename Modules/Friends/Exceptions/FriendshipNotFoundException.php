<?php

namespace Modules\Friends\Exceptions;

use Exception;

class FriendshipNotFoundException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('friends::exceptions.friendships.friendshipNotFoundException'), $this->code);
    }
}
