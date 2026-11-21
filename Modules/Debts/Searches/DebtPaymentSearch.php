<?php

namespace Modules\Debts\Searches;

use App\Searches\ModuleSearch;

class DebtPaymentSearch extends ModuleSearch
{
    protected array $fields = ['debt_payments_view.debtName', 'debt_payments_view.description'];

    protected array $relations = [];
}
