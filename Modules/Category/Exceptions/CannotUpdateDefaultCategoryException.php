<?php

namespace Modules\Category\Exceptions;

use Exception;

class CannotUpdateDefaultCategoryException extends Exception
{
    protected $code = 403;
    protected $message;

    public function __construct()
    {
        parent::__construct(__('category::exceptions.categories.cannotUpdateDefaultCategoryException'), $this->code);
    }
}
