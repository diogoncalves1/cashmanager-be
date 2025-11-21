<?php

namespace Modules\Accounts\Database\Factories;

use Modules\Accounts\Entities\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Transaction;
use Modules\Category\Entities\Category;
use Modules\Currency\Entities\Currency;
use Modules\User\Entities\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Accounts\Entities\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::pluck('id')->random(),
            'account_id' => Account::pluck('id')->random(),
            'type' => $this->faker->randomElement(['revenue', 'expense']),
            'amount' => $this->faker->randomFloat(2, 0, 100000),
            'date' => $this->faker->date('Y-m-d'),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['completed', 'pending']),
            'category_id' => Category::pluck('id')->random(),
        ];
    }
}
