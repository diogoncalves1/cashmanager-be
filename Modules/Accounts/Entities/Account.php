<?php
namespace Modules\Accounts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Currency\Entities\Currency;
use Modules\SharedRoles\Traits\IsShareable;
use Modules\User\Entities\User;

class Account extends Model
{
    /** @use HasFactory<\Modules\Accounts\Database\Factories\AccountFactory> */
    use HasFactory, IsShareable;

    protected $table    = "accounts";
    protected $fillable = ['name', 'type', 'balance', 'currency_id', 'active'];
    protected $casts    = [
        'active' => 'bool',
    ];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\Factories\AccountFactory::new ();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'account_users', 'account_id', 'user_id')
            ->using(\Modules\Accounts\Entities\AccountUser::class)
            ->withPivot('shared_role_id');
    }

    public function scopeActive($query, $active)
    {
        return $query->where('active', $active);
    }
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
    public function scopeJoinSharedRoles($query)
    {
        return $query->join('shared_roles', 'account_users.shared_role_id', '=', 'shared_roles.id');
    }

    public function creator()
    {
        $creatorRoleId = DB::table("shared_roles")->where("code", "creator")->value('id');

        return $this->users()->wherePivot('shared_role_id', $creatorRoleId)->first();
    }

    public static function getUserAccounts(string $id, array $permissions)
    {
        $query = self::query()->join("account_users", "account_users.account_id", '=', 'accounts.id')
            ->join("shared_roles", "shared_roles.id", '=', "account_users.shared_role_id")
            ->join('shared_role_permissions', "shared_role_permissions.shared_role_id", '=', 'shared_roles.id')
            ->join('shared_permissions', "shared_permissions.id", '=', 'shared_role_permissions.shared_permission_id')
            ->where("account_users.user_id", $id)
            ->select("accounts.*");

        foreach ($permissions as $permission) {
            $query->where("shared_permissions.code", $permission);
        }

        return $query->get();
    }
}
