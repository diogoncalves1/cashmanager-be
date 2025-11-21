<?php
namespace Modules\Accounts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\AccountUser;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Accounts\Entities\AccountUser>
 */
class AccountUserFactory extends Factory
{
    protected $model = AccountUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'        => User::pluck('id')->random(),
            'account_id'     => Account::pluck('id')->random(),
            'shared_role_id' => SharedRole::pluck('id')->random(),
        ];
    }
}
