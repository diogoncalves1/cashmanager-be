<?php
namespace Modules\SharedRoles\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedRole extends Model
{
    /** @use HasFactory<\Database\Factories\SharedRoleFactory> */
    use HasFactory;

    protected $table    = "shared_roles";
    protected $fillable = ["name", "code"];
    protected $guarded  = ["id"];
    protected $casts    = [
        'name' => 'object',
    ];

    protected static function newFactory()
    {
        return \Modules\SharedRoles\Database\Factories\SharedRoleFactory::new ();
    }

    public function permissions()
    {
        return $this->belongsToMany(SharedPermission::class, "shared_permission_roles", "shared_role_id", "shared_permission_id");
    }

    public function hasPermission(string | array $permissions)
    {
        return $this->permissions()->where("code", $permissions)->exists();
    }

    public function hasPermissions(string | array $permissions)
    {
        if (is_array($permissions)) {
            return $this->permissions()->whereIn('code', $permissions)->exists();
        }

        return $this->permissions()->where("code", $permissions)->exists();
    }
}
