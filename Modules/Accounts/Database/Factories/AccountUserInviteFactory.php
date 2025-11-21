<?php
namespace Modules\Accounts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Account;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class AccountUserInviteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Accounts\Entities\AccountUserInvite::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id'        => User::pluck('id')->random(),
            'account_id'     => Account::pluck('id')->random(),
            'shared_role_id' => SharedRole::pluck('id')->random(),
            'status'         => $this->faker->randomElement(['pending', 'revoked']),
        ];
    }
}
