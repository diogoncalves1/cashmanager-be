<?php

namespace Modules\Debts\Searches;

use App\Searches\ModuleSearch;

class DebtSearch extends ModuleSearch
{
    protected array $fields = ['debts_view.name', 'debts_view.description'];

    protected array $relations = [];
}
