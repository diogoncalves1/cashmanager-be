<?php

namespace Modules\UserPreferences\Observers;

use Modules\User\Entities\User;
use Modules\UserPreferences\Entities\UserPrefence;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $input = [
            'user_id' => $user->id,
            'currency_id' => 1,
            'lang' => 'en'
        ];

        UserPrefence::create($input);
    }
}
