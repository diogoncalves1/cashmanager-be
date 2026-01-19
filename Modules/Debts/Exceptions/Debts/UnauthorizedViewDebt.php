<?php
namespace Modules\Debts\Exceptions\Debts;

use Exception;

class UnauthorizedViewDebt extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debts.unauthorizedViewDebt'), $this->code);
    }
}
