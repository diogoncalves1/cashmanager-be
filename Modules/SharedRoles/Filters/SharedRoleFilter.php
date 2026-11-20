<?php

namespace Modules\SharedRoles\Filters;

use App\Filters\ModuleFilter;

class SharedRoleFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['code' => ['eq']];

    protected array $translateFields = ['code' => 'shared_roles.code'];

    protected array $aliases = [];

    protected array $valueRules = [];
}
