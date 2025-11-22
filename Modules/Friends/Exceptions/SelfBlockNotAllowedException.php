<?php

namespace Modules\Friends\Exceptions;

use Exception;

class SelfBlockNotAllowedException extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('friends::exceptions.friendships.selfBlockNotAllowedException'), $this->code);
    }
}
