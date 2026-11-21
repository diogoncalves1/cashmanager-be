<?php

namespace Modules\Debts\Filters;

use App\Filters\ModuleFilter;

class DebtFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['status' => ['eq']];

    protected array $translateFields = ['status' => 'debts_view.status'];

    protected array $aliases = [];

    protected array $valueRules = [];
}
