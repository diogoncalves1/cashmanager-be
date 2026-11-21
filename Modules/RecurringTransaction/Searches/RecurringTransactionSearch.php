<?php

namespace Modules\RecurringTransaction\Searches;

use App\Searches\ModuleSearch;

class RecurringTransactionSearch extends ModuleSearch
{
    protected array $fields = ['recurring_transactions.title', 'recurring_transactions.description'];

    protected array $relations = ['account' => ['accounts.name']];
}
