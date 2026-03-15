<?php
namespace Modules\FinancialGoal\Entities;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\SharedRoles\Entities\SharedRole;

class UserFinancialGoalContributionsView extends Pivot
{
    protected $table = "user_financial_goal_contributions_view";

    protected $casts = [
        'totalContributed' => 'float',
    ];

    public function financialGoal()
    {
        return $this->belongsTo(FinancialGoal::class, 'goalId');
    }

    public function sharedRole()
    {
        return $this->belongsTo(SharedRole::class, 'sharedRoleId');
    }
}
