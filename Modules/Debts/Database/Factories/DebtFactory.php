<?php
namespace Modules\Debts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Currency\Entities\Currency;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Debt>
 */
class DebtFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Debts\Entities\Debt::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalAmount = $this->faker->randomFloat('2');
        $paidAmount  = $totalAmount - $this->faker->randomFloat(2, 0, $totalAmount);

        $dueDate   = $this->faker->date();
        $startDate = $this->faker->date('Y-m-d', $dueDate);

        $months = $this->faker->randomNumber(2);

        return [
            'currency_id'    => Currency::pluck('id')->random(),
            'name'           => $this->faker->word(),
            'total_amount'   => $totalAmount,
            'paid_amount'    => $paidAmount,
            'status'         => $paidAmount == $totalAmount ? 'paid' : 'pending',
            'months'         => $months,
            'interest_rate'  => $this->faker->randomFloat(5, 0, 10),
            'start_date'     => $startDate,
            'due_date'       => $dueDate,
            'paid_at'        => $paidAmount == $totalAmount ? $this->faker->date('Y-m-d', $dueDate) : null,
            'months_paid'    => $this->faker->randomNumber(2),
            'description'    => $this->faker->sentence(),
            'is_recurring'   => $this->faker->boolean(),
            'monthly_amount' => ($totalAmount) / $months,
            'type'           => $this->faker->randomElement(['loan', 'credit_card', 'bill', 'other']),
        ];
    }
}
