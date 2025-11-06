<?php

namespace Modules\SharedRoles\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedPermission extends Model
{
    /** @use HasFactory<\Database\Factories\SharedPermissionFactory> */
    use HasFactory;

    protected $table = "shared_permissions";
    protected $fillable = ["code", "name", "category"];
    protected $guarded = ["id"];

    protected static function newFactory()
    {
        return \Modules\SharedRoles\Database\Factories\SharedPermissionFactory::new();
    }

    public function roles()
    {
        return $this->belongsToMany(SharedPermission::class, "shared_permission_roles", "shared_permission_id", "shared_role_id");
    }
}
