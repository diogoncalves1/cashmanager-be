<?php

namespace Modules\SharedRoles\Traits;

use Modules\SharedRoles\Repositories\SharedRoleRepository;
use Modules\User\Entities\User;

trait IsShareable
{
    public function userSharedRole($model, ?string $userId = null, ?User $user = null)
    {
        if ($userId) {
            $user = $model->relationLoaded('users')
                ? $model->users->firstWhere('id', $userId)
                : $model->users()->where('user_id', $userId)->first();
        }

        if (! $user) {
            return null;
        }

        $roleId = $user->pivot?->shared_role_id;

        return $roleId ? app(SharedRoleRepository::class)->show((string) $roleId) : null;
    }
}
