<?php

namespace Modules\Accounts\Filters;

use App\Filters\ModuleFilter;

class TransactionFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['status' => ['eq'], 'category_id' => ['eq', 'in'], 'account_id' => ['eq', 'in'], 'user_id' => ['eq'], 'type' => ['eq'], 'date' => ['gte', 'lte']];

    protected array $translateFields = ['status' => 'transactions.status', 'category_id' => 'transactions.category_id', 'account_id' => 'transactions.account_id', 'user_id' => 'transactions.user_id', 'type' => 'transactions.type', 'date' => 'transactions.date'];

    protected array $aliases = ['categoryId' => ['category_id', 'eq'], 'accountId' => ['account_id', 'eq'], 'userId' => ['user_id', 'eq'], 'user' => ['user_id', 'eq'], 'dateFrom' => ['date', 'gte'], 'dateTo' => ['date', 'lte']];

    protected array $valueRules = ['date' => ['date_format:Y-m-d'], 'account_id' => ['integer', 'min:1'], 'user_id' => ['integer', 'min:1'], 'category_id' => ['integer', 'min:1']];
}
