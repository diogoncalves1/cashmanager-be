<?php

namespace Modules\Accounts\Searches;

use App\Searches\ModuleSearch;

class AccountSearch extends ModuleSearch
{
    protected array $fields = ['accounts.name'];

    protected array $relations = [];
}
