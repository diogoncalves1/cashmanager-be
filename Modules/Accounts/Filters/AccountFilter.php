<?php

namespace Modules\Accounts\Filters;

use App\Filters\ModuleFilter;

class AccountFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['type' => ['eq'], 'active' => ['eq'], 'currency_id' => ['eq']];

    protected array $translateFields = ['type' => 'accounts.type', 'active' => 'accounts.active', 'currency_id' => 'accounts.currency_id'];

    protected array $aliases = ['currencyId' => ['currency_id', 'eq']];

    protected array $valueRules = ['active' => ['boolean'], 'currency_id' => ['integer', 'min:1']];
}
