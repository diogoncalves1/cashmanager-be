<?php
namespace Modules\Currency\Core;

use Modules\Currency\Entities\Currency;

class Helpers
{

    public static function convertCurrency(float $amount, Currency $fromCurrency, Currency $toCurrency)
    {
        if ($fromCurrency->code == $toCurrency->code) {
            return $amount;
        }

        $convertedAmount = $amount * ($toCurrency->rate / $fromCurrency->rate);

        return $convertedAmount;
    }
}
