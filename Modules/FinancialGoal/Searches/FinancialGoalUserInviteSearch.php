<?php

namespace Modules\FinancialGoal\Searches;

use App\Searches\ModuleSearch;

class FinancialGoalUserInviteSearch extends ModuleSearch
{
    protected array $fields = [];

    protected array $relations = ['sender' => ['users.name'], 'user' => ['users.name'], 'financialGoal' => ['name']];
}
