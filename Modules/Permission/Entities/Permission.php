<?php

namespace Modules\Permission\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionFactory> */
    use HasFactory;

    protected $fillable = ["name", "code", "category", "visible"];

    protected static function newFactory()
    {
        return \Modules\Permission\Database\Factories\PermissionFactory::new();
    }


    /**
     * Get the permissions of the given user
     *
     * @param $userId
     * @return array|null
     */
    public function permissions($userId)
    {
        $permissions = null;
        $collection = $this
            ->select(['permissions.code'])
            ->join('role_permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->join('roles', 'roles.id', '=', 'role_permissions.role_id')
            ->join('role_user', 'role_user.role_id', '=', 'roles.id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->where('users.id', $userId)
            ->groupby('permissions.code')
            ->get();
        foreach ($collection as $item) {
            $permissions[] = $item->code;
        }
        return $permissions;
    }

    public function roles()
    {
        return  $this->belongsToMany(Role::class);
    }
}
