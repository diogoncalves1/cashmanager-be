<?php

namespace Modules\SharedRoles\Traits;


use Modules\SharedRoles\Entities\SharedRole;

trait IsShareable
{
    public function userSharedRole($model, string $userId)
    {
        $user = $model->users()
            ->where('user_id', $userId)
            ->where('status', 'accepted')
            ->first();

        if (!$user)
            return null;

        return SharedRole::where('id', $user?->pivot->shared_role_id)->first();
    }
}
