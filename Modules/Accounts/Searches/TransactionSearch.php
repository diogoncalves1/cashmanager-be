<?php

namespace Modules\Accounts\Searches;

use App\Searches\ModuleSearch;

class TransactionSearch extends ModuleSearch
{
    protected array $fields = ['transactions.description'];

    protected array $relations = ['account' => ['accounts.name'], 'user' => ['users.name']];
}
