<?php

namespace Modules\User\Filters;

use App\Filters\ModuleFilter;

class SearchUserFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = [];

    protected array $translateFields = [];

    protected array $aliases = [];

    protected array $valueRules = [];
}
