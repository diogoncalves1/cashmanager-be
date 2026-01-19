<?php
namespace Modules\Debts\Entities;

class DebtsView extends Debt
{

    protected $table = 'debts_view';

    protected $casts = [
        'totalAmount'   => 'float',
        'paidAmount'    => 'float',
        'monthlyAmount' => 'float',
        'interestRate'  => 'float',
        'isRecurring'   => 'boolean',
    ];

    public function debt()
    {
        return $this->belongsTo(Debt::class, 'id', 'id');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where("debts_view.status", $status);
    }
    public function scopeType($query, $type)
    {
        return $query->where("debts_view.type", $type);
    }
}
