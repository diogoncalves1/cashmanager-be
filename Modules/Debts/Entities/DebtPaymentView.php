<?php
namespace Modules\Debts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;

// use Modules\Debts\Database\Factories\DebtPaymentViewFactory;

class DebtPaymentView extends DebtPayment
{
    use HasFactory;

    protected $table = "debt_payments_view";

    public function debt()
    {
        return $this->belongsTo(Debt::class, "debtId");
    }
}
