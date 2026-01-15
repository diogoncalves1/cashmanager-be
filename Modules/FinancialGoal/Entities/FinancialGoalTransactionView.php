<?php
namespace Modules\FinancialGoal\Entities;

class FinancialGoalTransactionView extends FinancialGoalTransaction
{
    protected $table = 'financial_goal_transaction_view';

    protected $casts = [
        'amount' => 'float',
    ];

    public function financialGoal()
    {
        return $this->belongsTo(FinancialGoal::class, 'financialGoalId');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('financial_goal_transaction_view.status', $status);
    }
    public function scopeType($query, $type)
    {
        return $query->where('financial_goal_transaction_view.type', $type);
    }
    public function scopeUser($query, $userId)
    {
        return $query->where('financial_goal_transaction_view.userId', $userId);
    }
    public function scopeFinancialGoal($query, $financialGoalId)
    {
        return $query->where('financial_goal_transaction_view.financialGoalId', $financialGoalId);
    }
}
