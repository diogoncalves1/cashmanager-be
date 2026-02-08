<?php
namespace Modules\Debts\Entities;

use Modules\User\Entities\User;

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

    public function userPayments()
    {
        return $this->belongsToMany(User::class, 'user_debt_payment_view', 'debtId', 'userId', )
            ->using(\Modules\Debts\Entities\DebtUser::class)
            ->withPivot('shared_role_id')
            ->withPivot('totalPaid')
            ->withPivot('currencyCode')
            ->withPivot('currencySymbol');
    }

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
