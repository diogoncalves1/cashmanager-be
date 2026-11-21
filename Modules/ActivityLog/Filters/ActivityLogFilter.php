<?php

namespace Modules\ActivityLog\Filters;

use App\Filters\ModuleFilter;

class ActivityLogFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['user_id' => ['eq']];

    protected array $translateFields = ['user_id' => 'activity_logs.user_id'];

    protected array $aliases = ['userId' => ['user_id', 'eq']];

    protected array $valueRules = ['user_id' => ['integer', 'min:1']];
}
