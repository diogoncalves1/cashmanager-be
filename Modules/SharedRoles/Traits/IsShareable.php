<?php
namespace Modules\SharedRoles\Traits;

use Modules\SharedRoles\Entities\SharedRole;

trait IsShareable
{
    public function userSharedRole($model, string $userId)
    {
        $user = $model->users()
            ->where('user_id', $userId)
            ->first();

        if (! $user) {
            return null;
        }

        return SharedRole::find($user?->pivot->shared_role_id);
    }
}
