<?php
namespace Modules\Accounts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class AccountUser extends Pivot
{
    /** @use HasFactory<\Modules\Accounts\Database\Factories\AccountUserFactory> */
    use HasFactory;

    protected $table    = 'account_users';
    protected $fillable = ['user_id', 'account_id', 'shared_role_id'];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\Factories\AccountUserFactory::new ();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function sharedRole(): BelongsTo
    {
        return $this->belongsTo(SharedRole::class, 'shared_role_id');
    }

    public function scopeUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    public function scopeAccount($query, $accountId)
    {
        return $query->where("account_id", $accountId);
    }
}
