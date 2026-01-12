<?php
namespace Modules\FinancialGoal\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\FinancialGoal\Entities\FinancialGoalUser;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class FinancialGoalUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = FinancialGoalUser::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'financial_goal_id' => FinancialGoal::pluck('id')->random(),
            'user_id'           => User::pluck('id')->random(),
            'shared_role_id'    => SharedRole::pluck('id')->random(),
        ];
    }
}
