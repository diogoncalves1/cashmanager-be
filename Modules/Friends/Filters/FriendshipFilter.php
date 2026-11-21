<?php

namespace Modules\Friends\Filters;

use App\Filters\ModuleFilter;

class FriendshipFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = [];

    protected array $translateFields = [];

    protected array $aliases = [];

    protected array $valueRules = [];
}
