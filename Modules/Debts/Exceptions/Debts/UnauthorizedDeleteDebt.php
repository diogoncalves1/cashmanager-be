<?php
namespace Modules\Debts\Exceptions\Debts;

use Exception;

class UnauthorizedDeleteDebt extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debts.unauthorizedDeleteDebt'), $this->code);
    }
}
