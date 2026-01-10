<?php
namespace Modules\FinancialGoal\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Currency\Entities\Currency;
use Modules\FinancialGoal\Entities\FinancialGoal;

class FinancialGoalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = FinancialGoal::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $totalAmount       = $this->faker->randomFloat(2);
        $contributedAmount = $this->faker->randomFloat(2, 0, $totalAmount);
        $dueDate           = $this->faker->date();
        $status            = $contributedAmount == $totalAmount ? 'completed' : $this->faker->randomElement(['in_progress', 'canceled']);

        return [
            'currency_id'        => Currency::pluck('id')->random(),
            'name'               => $this->faker->word(),
            'total_amount'       => $totalAmount,
            'contributed_amount' => $contributedAmount,
            'start_date'         => $this->faker->date(max: $dueDate),
            'due_date'           => $dueDate,
            'status'             => $status,
            'description'        => $this->faker->sentence(),
            'completed_at'       => $status == 'completed' ? $this->faker->date(max : $dueDate): null,
            'priority'           => $this->faker->randomElement(['low', 'medium', 'high']),
            'canceled_at'        => $status == 'canceled' ? $this->faker->date(max : $dueDate): null,
        ];
    }
}
