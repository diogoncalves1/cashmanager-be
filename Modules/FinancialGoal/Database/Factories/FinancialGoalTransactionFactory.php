<?php
namespace Modules\FinancialGoal\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\User\Entities\User;

class FinancialGoalTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = FinancialGoalTransaction::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {

        return [
            'financial_goal_id' => FinancialGoal::pluck('id')->random(),
            'user_id'           => User::pluck('id')->random(),
            'transaction_id'    => null,
            'type'              => $this->faker->randomElement(['withdrawal', 'contribution']),
            'amount'            => $this->faker->randomFloat(2),
            'date'              => $this->faker->date(),
            'description'       => $this->faker->sentence(),
            'status'            => $this->faker->randomElement(['pending', 'completed']),
        ];
    }
}
