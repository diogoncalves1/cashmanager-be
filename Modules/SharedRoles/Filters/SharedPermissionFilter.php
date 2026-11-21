<?php

namespace Modules\SharedRoles\Filters;

use App\Filters\ModuleFilter;

class SharedPermissionFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['code' => ['eq']];

    protected array $translateFields = ['code' => 'shared_permissions.code'];

    protected array $aliases = [];

    protected array $valueRules = [];
}
