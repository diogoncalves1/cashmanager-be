<?php
namespace Modules\Debts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Debts\Database\Factories\DebtUserInviteFactory;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class DebtUserInvite extends Model
{
    /** @use HasFactory<\Modules\Detbs\Database\Factories\DebtUserInviteFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'debt_id', 'shared_role_id', 'status', 'invited_by_id'];
    protected $table    = "debt_user_invites";

    protected static function newFactory(): DebtUserInviteFactory
    {
        return DebtUserInviteFactory::new ();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }
    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }
    public function sharedRole()
    {
        return $this->belongsTo(SharedRole::class);
    }

    public function scopeUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    public function scopeDebt($query, $debtId)
    {
        return $query->where("debt_id", $debtId);
    }
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    public function scopeInvitedBy($query, $userId)
    {
        return $query->where("invited_by_id", $userId);
    }
}
