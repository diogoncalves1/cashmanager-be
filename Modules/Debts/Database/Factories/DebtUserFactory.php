<?php
namespace Modules\Debts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Debts\Entities\Debt;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class DebtUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Debts\Entities\DebtUser::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id'        => User::pluck('id')->random(),
            'debt_id'        => Debt::pluck('id')->random(),
            'shared_role_id' => SharedRole::pluck('id')->random(),
        ];
    }
}
