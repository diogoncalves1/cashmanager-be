<?php
namespace Modules\Debts\Entities;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\SharedRoles\Entities\SharedRole;

// use Modules\Debts\Database\Factories\UserDebtPaymentViewFactory;

class UserDebtPaymentView extends Pivot
{

    protected $table = "user_debt_payment_view";

    protected $casts = [
        'totalContributed' => 'float',
    ];

    public function debt()
    {
        return $this->belongsTo(Debt::class, 'debtId');
    }

    public function sharedRole()
    {
        return $this->belongsTo(SharedRole::class, 'sharedRoleId');
    }
}
