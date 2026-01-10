<?php
namespace Modules\FinancialGoal\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounts\Entities\Transaction;
use Modules\User\Entities\User;

class FinancialGoalTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['financial_goal_id', 'user_id', 'transaction_id', 'type', 'amount', 'date', 'description', 'status'];
    protected $table    = 'financial_goal_transactions';

    protected static function newFactory()
    {
        return \Modules\FinancialGoal\Database\Factories\FinancialGoalTransactionFactory::new ();
    }

    public function financialGoal()
    {
        return $this->belongsTo(FinancialGoal::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
