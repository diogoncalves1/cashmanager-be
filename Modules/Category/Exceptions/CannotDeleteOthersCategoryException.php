<?php

namespace Modules\Category\Exceptions;

use Exception;

class CannotDeleteOthersCategoryException extends Exception
{
    protected $code = 403;
    protected $message;

    public function __construct()
    {
        parent::__construct(__('category::exceptions.categories.cannotDeleteOthersCategoryException'), $this->code);
    }
}
