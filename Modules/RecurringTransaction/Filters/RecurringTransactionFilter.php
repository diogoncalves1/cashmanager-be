<?php
namespace Modules\RecurringTransaction\Filters;

use App\Filters\Filter;

class RecurringTransactionFilter extends Filter
{
    protected array $allowedOperatorsFields = [
        'type'       => ['eq'],
        'categoryId' => ['eq'],
        'isActive'   => ['eq'],
        'is_active'  => ['eq'],
        'frequency'  => ['eq'],
        'accountId'  => ['eq'],
        'userId'     => ['eq'],
        'startDate'  => ['gte', 'lte'],
        'endDate'    => ['gte', 'lte'],
    ];

    protected array $translateFields = [
        'type'       => 'recurring_transactions.type',
        'categoryId' => 'recurring_transactions.category_id',
        'isActive'   => 'recurring_transactions.is_active',
        'is_active'  => 'recurring_transactions.is_active',
        'frequency'  => 'recurring_transactions.frequency',
        'accountId'  => 'recurring_transactions.account_id',
        'userId'     => 'recurring_transactions.user_id',
        'startDate'  => 'recurring_transactions.start_date',
        'endDate'    => 'recurring_transactions.end_date',
    ];
}
