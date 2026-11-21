<?php

namespace Modules\FinancialGoal\Filters;

use App\Filters\ModuleFilter;

class FinancialGoalTransactionFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['type' => ['eq'], 'status' => ['eq'], 'user_id' => ['eq'], 'financial_goal_id' => ['eq'], 'date' => ['gte', 'lte']];

    protected array $translateFields = ['type' => 'financial_goal_transaction_view.type', 'status' => 'financial_goal_transaction_view.status', 'user_id' => 'financial_goal_transaction_view.userId', 'financial_goal_id' => 'financial_goal_transaction_view.financialGoalId', 'date' => 'financial_goal_transaction_view.date'];

    protected array $aliases = ['userId' => ['user_id', 'eq'], 'financialGoalId' => ['financial_goal_id', 'eq'], 'dateFrom' => ['date', 'gte'], 'dateTo' => ['date', 'lte']];

    protected array $valueRules = ['date' => ['date_format:Y-m-d'], 'user_id' => ['integer', 'min:1'], 'financial_goal_id' => ['integer', 'min:1']];
}
