<?php
namespace Modules\Debts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Transaction;
use Modules\Debts\Entities\Debt;
use Modules\User\Entities\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DebtPayment>
 */
class DebtPaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Debts\Entities\DebtPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'completed']);

        return [
            "transaction_id" => $status == 'completed' ? Transaction::pluck("id")->random() : null,
            "debt_id"        => Debt::pluck("id")->random(),
            "user_id"        => User::pluck("id")->random(),
            'date'           => $this->faker->date(),
            "status"         => $status,
            'amount'         => $this->faker->randomFloat(2),
            'description'    => $this->faker->sentence(),
        ];
    }
}
