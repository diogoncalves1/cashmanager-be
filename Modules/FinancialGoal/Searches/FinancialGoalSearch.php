<?php

namespace Modules\FinancialGoal\Searches;

use App\Searches\ModuleSearch;

class FinancialGoalSearch extends ModuleSearch
{
    protected array $fields = ['financial_goal_view.name', 'financial_goal_view.description'];

    protected array $relations = [];
}
