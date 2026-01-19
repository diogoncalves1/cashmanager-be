<?php
namespace Modules\Debts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Currency\Entities\Currency;
use Modules\SharedRoles\Traits\IsShareable;
use Modules\User\Entities\User;

class Debt extends Model
{
    /** @use HasFactory<\Modules\Debts\Database\Factories\DebtFactory> */
    use HasFactory, IsShareable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "currency_id", "name", "total_amount", "paid_amount", "status", "months",
        "interest_rate", "start_date", "due_date", "paid_at", "months_paid",
        "description", "monthly_amount",
    ];
    protected $casts = [
        'interest_rate' => 'float',
    ];

    protected static function newFactory()
    {
        return \Modules\Debts\Database\Factories\DebtFactory::new ();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'debt_users', 'debt_id', 'user_id')
            ->using(\Modules\Debts\Entities\DebtUser::class)
            ->withPivot('shared_role_id');
    }
    public function payments()
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where("status", $status);
    }
}
