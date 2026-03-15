<?php
namespace Modules\ActivityLog\Exceptions;

use Exception;

class ActivityLogTypeInvalid extends Exception
{
    protected $message;
    protected $code = 500;

    public function __construct()
    {
        parent::__construct(__('activitylog::exceptions.activity-logs.activityLogTypeInvalid'), $this->code);
    }
}
