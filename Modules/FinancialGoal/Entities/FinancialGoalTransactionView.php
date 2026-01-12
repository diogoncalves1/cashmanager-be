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
        return $query->where('status', $status);
    }
}
