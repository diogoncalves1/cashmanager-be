<?php
namespace Modules\Accounts\Core;

use DateTime;

class Helpers
{
    public static function getAccountIcon($type)
    {
        switch ($type) {
            case "cash":{
                    return "cash";
                }
            case "bank_account":{
                    return "building-bank";
                }
            case "credit_card":{
                    return "credit-card";
                }
            case "digital_wallet":{
                    return "wallet";
                }
        }
    }

    public static function formatMoney($amount)
    {
        return number_format($amount, 2, '.', ',');
    }

    public static function formatMoneyWithSymbolAndCurrency(float $amount, string $currencyCode, string $currencySymbol): string
    {
        $currenciesCodesPrefix = config('currency.currenciesCodesPrefix');

        $amountFormated = self::formatMoneyWithSymbol($amount);

        if (in_array($currencyCode, $currenciesCodesPrefix)) {
            return $currencySymbol . ' ' . $amountFormated;
        }

        return $amountFormated . ' ' . $currencySymbol;
    }

    public static function formatMoneyWithSymbol($amount): string
    {
        if ($amount < 1000) {
            return self::formatMoney($amount);
        }

        $units = ['', 'k', 'M', 'B', 'T'];
        $power = floor(log($amount, 1000));
        $value = $amount / pow(1000, $power);

        return self::formatMoney($value) . ' ' . $units[$power];
    }

    public static function getOldDate($daysToReduce = 0, $monthsToReduce = 0, $yearsToReduce = 0): DateTime
    {
        $date = new DateTime();

        $oldDate = $date->modify("-{$daysToReduce} days");
        $oldDate = $oldDate->modify("-{$monthsToReduce} months");
        $oldDate = $oldDate->modify("-{$yearsToReduce} years");

        return $oldDate;
    }

    public static function getClassByStatus($status)
    {
        if ($status == "pending") {
            return "warning";
        } else if ($status == "paid" || $status == "completed") {
            return "success";
        } else {
            return "danger";
        }

    }
}
