<?php

namespace Modules\Debts\Filters;

use App\Filters\ModuleFilter;

class DebtPaymentFilter extends ModuleFilter
{
    protected array $allowedOperatorsFields = ['status' => ['eq'], 'date' => ['gte', 'lte'], 'debt_id' => ['eq'], 'user_id' => ['eq']];

    protected array $translateFields = ['status' => 'debt_payments_view.status', 'date' => 'debt_payments_view.date', 'debt_id' => 'debt_payments_view.debtId', 'user_id' => 'debt_payments_view.userId'];

    protected array $aliases = ['debtId' => ['debt_id', 'eq'], 'user' => ['user_id', 'eq'], 'userId' => ['user_id', 'eq'], 'dateFrom' => ['date', 'gte'], 'dateTo' => ['date', 'lte']];

    protected array $valueRules = ['date' => ['date_format:Y-m-d'], 'debt_id' => ['integer', 'min:1'], 'user_id' => ['integer', 'min:1']];
}
