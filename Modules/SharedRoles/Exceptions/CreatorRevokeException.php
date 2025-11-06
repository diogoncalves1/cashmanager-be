<?php

namespace Modules\SharedRoles\Exceptions;

use Exception;

class CreatorRevokeException extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('sharedroles::exceptions.shareable.creatorRevokeException'), $this->code);
    }
}
