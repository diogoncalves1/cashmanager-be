<?php
namespace Modules\UserPreferences\Observers;

use Modules\UserPreferences\Entities\UserPrefence;
use Modules\User\Entities\User;
use Modules\User\Events\VerifyEmail;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $input = [
            'user_id'     => $user->id,
            'currency_id' => 1,
            'lang'        => 'en',
        ];

        event(new VerifyEmail($user));

        UserPrefence::create($input);
    }

}
