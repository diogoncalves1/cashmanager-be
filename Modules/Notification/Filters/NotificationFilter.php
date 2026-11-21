<?php

namespace Modules\Notification\Filters;

use App\Filters\ModuleFilter;

class NotificationFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = [];

    protected array $translateFields = [];

    protected array $aliases = [];

    protected array $valueRules = [];
}
