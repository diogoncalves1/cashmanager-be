<?php

namespace Modules\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Currency\Entities\Currency;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{

    protected $model = Currency::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code =  $this->faker->unique()->currencyCode();

        $currencies = config('currency.currencies');

        $symbol = $currencies[$code]['symbol'] ?? '$';
        $name = $currencies[$code]['name'] ?? '{"en": "United States Dollar"}';

        return [
            "code" => $code,
            "symbol" => $symbol,
            "name" => $name,
            "rate" => $this->faker->randomFloat(4, 0.1, 5)
        ];
    }
}
