<?php
namespace Modules\Debts\Entities;

use Illuminate\Database\Eloquent\Model;

class DebtBasicView extends Model
{
    protected $table = "debt_basic_view";

    public function scopeStatus($query, $status)
    {
        return $query->where('debt_basic_view.status', $status);
    }
}
