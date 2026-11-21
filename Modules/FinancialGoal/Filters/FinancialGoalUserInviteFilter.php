<?php

namespace Modules\FinancialGoal\Filters;

use App\Filters\ModuleFilter;

class FinancialGoalUserInviteFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['status' => ['eq']];

    protected array $translateFields = ['status' => 'financial_goal_user_invites.status'];

    protected array $aliases = [];

    protected array $valueRules = [];
}
