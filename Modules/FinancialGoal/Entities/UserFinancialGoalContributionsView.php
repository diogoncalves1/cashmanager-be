<?php
namespace Modules\FinancialGoal\Entities;

use Illuminate\Database\Eloquent\Model;

class UserFinancialGoalContributionsView extends Model
{
    protected $table = "user_financial_goal_contributions_view";

    protected $casts = [
        'totalContributed' => 'float',
    ];

    public function financialGoal()
    {
        return $this->belongsTo(FinancialGoal::class, 'goalId');
    }
}
