<?php

namespace Modules\FinancialGoal\Searches;

use App\Searches\ModuleSearch;

class FinancialGoalTransactionSearch extends ModuleSearch
{
    protected array $fields = ['financial_goal_transaction_view.description'];

    protected array $relations = [];
}
