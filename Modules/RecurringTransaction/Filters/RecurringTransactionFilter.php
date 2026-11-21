<?php

namespace Modules\RecurringTransaction\Filters;

use App\Filters\ModuleFilter;

class RecurringTransactionFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['type' => ['eq'], 'category_id' => ['eq'], 'is_active' => ['eq'], 'frequency' => ['eq'], 'account_id' => ['eq'], 'user_id' => ['eq'], 'start_date' => ['gte', 'lte'], 'end_date' => ['gte', 'lte']];

    protected array $translateFields = ['type' => 'recurring_transactions.type', 'category_id' => 'recurring_transactions.category_id', 'is_active' => 'recurring_transactions.is_active', 'frequency' => 'recurring_transactions.frequency', 'account_id' => 'recurring_transactions.account_id', 'user_id' => 'recurring_transactions.user_id', 'start_date' => 'recurring_transactions.start_date', 'end_date' => 'recurring_transactions.end_date'];

    protected array $aliases = ['categoryId' => ['category_id', 'eq'], 'isActive' => ['is_active', 'eq'], 'accountId' => ['account_id', 'eq'], 'userId' => ['user_id', 'eq'], 'startDate' => ['start_date', 'gte'], 'endDate' => ['end_date', 'lte']];

    protected array $valueRules = ['is_active' => ['boolean'], 'account_id' => ['integer', 'min:1'], 'category_id' => ['integer', 'min:1'], 'user_id' => ['integer', 'min:1'], 'start_date' => ['date_format:Y-m-d'], 'end_date' => ['date_format:Y-m-d']];
}
