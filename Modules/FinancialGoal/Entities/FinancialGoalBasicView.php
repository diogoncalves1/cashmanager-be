<?php
namespace Modules\FinancialGoal\Entities;

class FinancialGoalBasicView extends FinancialGoal
{

    protected $table = "financial_goal_basic_view";

    public function scopeStatus($query, $status)
    {
        return $query->where('financial_goal_basic_view.status', $status);
    }
}
