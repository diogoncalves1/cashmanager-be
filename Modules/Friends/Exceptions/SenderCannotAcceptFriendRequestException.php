<?php

namespace Modules\Friends\Exceptions;

use Exception;

class SenderCannotAcceptFriendRequestException extends Exception
{
    protected $message;
    protected $code = 402;

    public function __construct()
    {
        parent::__construct(__('friends::exceptions.friendships-requests.senderCannotAcceptFriendRequestException'), $this->code);
    }
}
