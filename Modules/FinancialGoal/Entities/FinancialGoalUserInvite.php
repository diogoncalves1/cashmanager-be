<?php
namespace Modules\FinancialGoal\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\FinancialGoal\Database\Factories\FinancialGoalUserInviteFactory;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

// use Modules\FinancialGoal\Database\Factories\FinancialGoalUserInviteFactory;

class FinancialGoalUserInvite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['shared_role_id', 'financial_goal_id', 'user_id', 'status'];
    protected $table    = 'financial_goal_user_invites';

    protected static function newFactory(): FinancialGoalUserInviteFactory
    {
        return FinancialGoalUserInviteFactory::new ();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function financialGoal()
    {
        return $this->belongsTo(FinancialGoal::class);
    }
    public function sharedRole()
    {
        return $this->belongsTo(SharedRole::class);
    }

    public function scopeUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    public function scopeFinancialGoal($query, $financialGoalId)
    {
        return $query->where("financial_goal_id", $financialGoalId);
    }
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
