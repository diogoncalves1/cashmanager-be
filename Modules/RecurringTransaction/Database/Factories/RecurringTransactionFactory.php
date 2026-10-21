<?php
namespace Modules\RecurringTransaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Account;
use Modules\Category\Entities\Category;
use Modules\User\Entities\User;

class RecurringTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\RecurringTransaction\Entities\RecurringTransaction::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $frequency = $this->faker->randomElement(['daily', 'weekly', 'monthly', 'yearly', 'custom']);
        $isLinked  = $this->faker->boolean(30);

        $linkedTypes = ['Modules\Debts\Entities\Debt', 'Modules\FinancialGoal\Entities\FinancialGoal'];
        $linkedType  = $isLinked ? $this->faker->randomElement($linkedTypes) : null;
        $linkedId    = $isLinked ? app($linkedType)->pluck('id')->random() : null;

        return [
            'account_id'            => Account::pluck('id')->random(),
            'category_id'           => Category::pluck('id')->random(),
            'user_id'               => User::pluck('id')->random(),

            'type'                  => $this->faker->randomElement(['revenue', 'expense']),
            'amount'                => $this->faker->randomFloat(2, 0, 100000),
            'description'           => $this->faker->sentence(),

            'frequency'             => $frequency,
            'custom_frequency_days' => $frequency === 'custom' ? $this->faker->numberBetween(1, 30) : null,
            'start_date'            => $this->faker->date('Y-m-d'),
            'end_date'              => $this->faker->boolean(50) ? $this->faker->date('Y-m-d') : null,
            'last_generated_at'     => $this->faker->boolean(50) ? $this->faker->date('Y-m-d H:i:s') : null,
            'is_active'             => $this->faker->boolean(80),

            'is_linked'             => $isLinked,
            'linked_type'           => $linkedType,
            'linked_id'             => $linkedId,
        ];
    }
}
