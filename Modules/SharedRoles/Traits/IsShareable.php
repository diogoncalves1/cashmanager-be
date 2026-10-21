<?php
namespace Modules\SharedRoles\Traits;

use Modules\SharedRoles\Repositories\SharedRoleRepository;

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

        return app(SharedRoleRepository::class)->show($user?->pivot->shared_role_id);
    }
}
