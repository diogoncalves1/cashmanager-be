<?php

namespace Modules\Friends\Exceptions;

use Exception;

class FriendRequestNotFoundException extends Exception
{
    protected $message;
    protected $code = 404;

    public function __construct()
    {
        parent::__construct(__('friends::exceptions.friendships-requests.friendRequestNotFoundException'), $this->code);
    }
}
