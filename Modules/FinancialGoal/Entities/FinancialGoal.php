<?php
namespace Modules\FinancialGoal\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Currency\Entities\Currency;
use Modules\SharedRoles\Traits\IsShareable;
use Modules\User\Entities\User;

class FinancialGoal extends Model
{
    use HasFactory, IsShareable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['currency_id', 'name', 'total_amount', 'contributed_amount', 'start_date', 'due_date', 'status', 'description', 'completed_at', 'priority', 'canceled_at', 'completed_funding'];
    protected $table    = 'financial_goals';
    protected $cats     = [
        'total_amount' => 'float',
    ];

    protected static function newFactory()
    {
        return \Modules\FinancialGoal\Database\Factories\FinancialGoalFactory::new ();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'financial_goal_users', 'financial_goal_id', 'user_id')
            ->using(\Modules\FinancialGoal\Entities\FinancialGoalUser::class)
            ->withPivot('shared_role_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function transactions()
    {
        return $this->hasMany(FinancialGoalTransaction::class);
    }
    public function transactionsView()
    {
        return $this->hasMany(FinancialGoalTransactionView::class, 'financialGoalId');
    }

    public function scopeJoinSharedRoles($query)
    {
        return $query->join('shared_roles', 'financial_goal_users.shared_role_id', '=', 'shared_roles.id');
    }
}
