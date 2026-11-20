<?php

namespace Modules\Accounts\Filters;

use App\Filters\ModuleFilter;

class AccountUserInviteFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['status' => ['eq']];

    protected array $translateFields = ['status' => 'account_user_invites.status'];

    protected array $aliases = [];

    protected array $valueRules = [];
}
