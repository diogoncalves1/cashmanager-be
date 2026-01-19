<?php
namespace Modules\Debts\Exceptions\Debts;

use Exception;

class DebtNotFoundException extends Exception
{
    protected $message;
    protected $code = 404;

    public function __construct()
    {
        parent::__construct(__('debts::exceptions.debts.debtNotFoundException'), $this->code);
    }
}
