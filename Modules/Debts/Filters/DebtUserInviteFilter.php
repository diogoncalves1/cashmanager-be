<?php

namespace Modules\Debts\Filters;

use App\Filters\ModuleFilter;

class DebtUserInviteFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['status' => ['eq']];

    protected array $translateFields = ['status' => 'debt_user_invites.status'];

    protected array $aliases = [];

    protected array $valueRules = [];
}
