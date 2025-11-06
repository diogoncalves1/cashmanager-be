<?php

namespace Modules\Permission\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    /** @use HasFactory<\Database\Factories\RolePermissionFactory> */
    use HasFactory;

    protected $table = 'role_permissions';

    protected $fillable = ["role_id", "permission_id"];
    public $timestamps = true;

    protected static function newFactory()
    {
        return \Modules\Permission\Database\Factories\RolePermissionFactory::new();
    }
}
