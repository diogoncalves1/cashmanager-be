<?php
namespace Modules\FinancialGoal\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class FinancialGoalUserInviteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\FinancialGoal\Entities\FinancialGoalUserInvite::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'financial_goal_id' => FinancialGoal::pluck('id')->random(),
            'user_id'           => User::pluck('id')->random(),
            'shared_role_id'    => SharedRole::pluck('id')->random(),
            'status'            => $this->faker->randomElement(['pending', 'revoked']),
        ];
    }
}
