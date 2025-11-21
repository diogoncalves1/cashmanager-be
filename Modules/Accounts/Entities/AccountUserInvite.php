<?php
namespace Modules\Accounts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class AccountUserInvite extends Model
{
    /** @use HasFactory<\Modules\Accounts\Database\Factories\AccountUserInviteFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'account_id', 'shared_role_id', 'status'];
    protected $table    = 'account_user_invites';

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\Factories\AccountUserInviteFactory::new ();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function sharedRole()
    {
        return $this->belongsTo(SharedRole::class);
    }

    public function scopeUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    public function scopeAccount($query, $accountId)
    {
        return $query->where("account_id", $accountId);
    }
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
