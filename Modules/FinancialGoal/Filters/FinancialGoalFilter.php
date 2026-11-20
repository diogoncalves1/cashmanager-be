<?php

namespace Modules\FinancialGoal\Filters;

use App\Filters\ModuleFilter;

class FinancialGoalFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['status' => ['eq'], 'priority' => ['eq']];

    protected array $translateFields = ['status' => 'financial_goal_view.status', 'priority' => 'financial_goal_view.priority'];

    protected array $aliases = [];

    protected array $valueRules = [];
}
