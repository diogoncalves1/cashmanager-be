<?php
namespace Modules\FinancialGoal\Entities;

use Modules\User\Entities\User;

class FinancialGoalView extends FinancialGoal
{
    protected $table = 'financial_goal_view';

    protected $casts = [
        'totalAmount'       => 'float',
        'contributedAmount' => 'float',
    ];

    public function financialGoal()
    {
        return $this->belongsTo(FinancialGoal::class, "id", "id");
    }
    public function userContributions()
    {
        return $this->belongsToMany(User::class, 'user_financial_goal_contributions_view', 'goalId', 'userId', )
            ->using(\Modules\FinancialGoal\Entities\FinancialGoalUser::class)
            ->withPivot('shared_role_id')
            ->withPivot('totalContributed')
            ->withPivot('currencyCode')
            ->withPivot('currencySymbol');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
}
