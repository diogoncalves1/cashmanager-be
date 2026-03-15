<?php
namespace Modules\Debts\Core;

class Helpers
{
    public static function debtInterestValue(float $totalAmount, float $monthlyInterestRate, int $months = 1)
    {
        return $totalAmount * (($monthlyInterestRate / 100) * $months);
    }
    public static function debtTotalAmount(float $totalAmount, float $monthlyInterestRate, int $months)
    {
        return $totalAmount + self::debtInterestValue($totalAmount, $monthlyInterestRate, $months);
    }

    public static function debtRemainingAmount(float $totalAmount, float $paidAmount, float $monthlyInterestRate, int $months)
    {
        return (self::debtTotalAmount($totalAmount, $monthlyInterestRate, $months) - $paidAmount);
    }

    public static function percentage(float $totalAmount = 0, float $contributedAmount = 0, int $decimals = 2): float
    {
        if ($totalAmount == 0) {
            return 0;
        }

        return (float) ((($contributedAmount - $totalAmount) / $totalAmount) * 100);
    }
}
