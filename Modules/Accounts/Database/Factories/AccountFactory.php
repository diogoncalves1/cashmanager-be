<?php

namespace Modules\Accounts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Account;
use Modules\Currency\Entities\Currency;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Accounts\Entities\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'type' => $this->faker->randomElement(['cash', 'bank_account', 'credit_card', 'digital_wallet']),
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'currency_id' => Currency::pluck('id')->random(),
            'active' => $this->faker->boolean(80)
        ];
    }
}
