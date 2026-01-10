<?php
namespace Modules\FinancialGoal\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class FinancialGoalUser extends Pivot
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'shared_role_id', 'financial_goal_id'];
    protected $table    = 'financial_goal_users';

    protected static function newFactory()
    {
        return \Modules\FinancialGoal\Database\Factories\FinancialGoalUserFactory::new ();
    }

    public function sharedRole()
    {
        return $this->belongsTo(SharedRole::class, 'shared_role_id');
    }
    public function financialGoal()
    {
        return $this->belongsTo(FinancialGoal::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    public function scopeFinancialGoal($query, $financialGoalId)
    {
        return $query->where("financial_goal_id", $financialGoalId);
    }
}
