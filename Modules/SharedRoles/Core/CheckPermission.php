<?php
namespace Modules\SharedRoles\Core;

use Illuminate\Support\Facades\Cache;
use Modules\SharedRoles\Entities\SharedRole;

class CheckPermission
{
    public static function check(?SharedRole $sharedRole, string | array $permissions): bool
    {
        if (! $sharedRole) {
            return false;
        }

        if (is_array($permissions)) {
            return $sharedRole->hasPermissions($permissions);
        } else {
            return Cache::remember("shared_role:{$sharedRole->id}:permission:{$permissions}",
                now()->addMinutes(5),
                function () use ($sharedRole, $permissions) {
                    return $sharedRole->hasPermissions($permissions);
                });
        }
    }

    public static function checkMultiple(?SharedRole $sharedRole, array $permissions): array
    {
        if (! $sharedRole) {
            return [];
        }

        $allowed = [];

        foreach ($permissions as $key => $permission) {
            $allowed[$key] = Cache::remember("shared_role:{$sharedRole->id}:permission:{$permission}",
                now()->addMinutes(5),
                function () use ($sharedRole, $permission) {
                    return $sharedRole->hasPermissions($permission);
                });
        }

        return $allowed;
    }
}
