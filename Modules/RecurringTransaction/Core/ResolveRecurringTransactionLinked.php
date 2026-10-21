<?php
namespace Modules\RecurringTransaction\Core;

use Modules\Debts\Entities\Debt;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\RecurringTransaction\Entities\RecurringTransaction;

class ResolveRecurringTransactionLinked
{
    private const LIKED_TYPE_MAP = [
        Debt::class          => \Modules\Debts\Http\Resources\DebtResource::class,
        FinancialGoal::class => \Modules\FinancialGoal\Http\Resources\FinancialGoalResource::class,
    ];

    public static function resolve(RecurringTransaction $transaction)
    {
        if ($transaction->is_linked) {
            $linked = $transaction->linked;

            if ($linked) {
                return self::LIKED_TYPE_MAP[$transaction->linked_type]::make($linked);
            }
        }

        return null;
    }
}
