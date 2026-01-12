<?php
namespace Modules\FinancialGoal\Core;

class Helpers
{
    public static function percentage(float $totalAmount, float $contributedAmount, int $decimals = 2): float
    {
        if ($contributedAmount == 0 || $totalAmount == 0) {
            return 0;
        }

        return (float) number_format(($contributedAmount / $totalAmount) * 100, $decimals);
    }
}
