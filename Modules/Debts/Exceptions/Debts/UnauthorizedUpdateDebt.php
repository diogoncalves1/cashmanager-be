<?php
namespace Modules\Debts\Exceptions\Debts;

use Exception;

class UnauthorizedUpdateDebt extends Exception
{
    protected $message;
    protected $code = 403;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debts.unauthorizedUpdateDebt'), $this->code);
    }
}
