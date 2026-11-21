<?php
namespace Modules\Accounts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Category\Entities\Category;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class Transaction extends Model
{
    /** @use HasFactory<\Modules\Accounts\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $table    = 'transactions';
    protected $fillable = ['user_id', 'account_id', 'type', 'amount', 'date', 'description', 'status', 'category_id', 'is_recurring', 'recurring_transaction_id' /*, 'currency_id'*/];

    protected static function booted(): void
    {
        static::addGlobalScope('goal_reservations', function ($query) {
            self::excludeNeutralizedGoals($query, $query->getModel()->qualifyColumn('id'));
        });
    }

    public static function excludeNeutralizedGoals($query, string $idColumn): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('financial_goal_neutralized_transactions')) {
            $query->whereNotExists(fn ($q) => $q->selectRaw('1')->from('financial_goal_neutralized_transactions as goal_history')
                ->whereColumn('goal_history.transaction_id', $idColumn));
        }
    }

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\Factories\TransactionFactory::new ();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function sharedRole()
    {
        return $this->hasOneThrough(
            SharedRole::class,
            AccountUser::class,
            'account_id',
            'id',
            'account_id',
            'shared_role_id'
        );
    }

    public function scopeType($query, $type)
    {
        return $query->where('transactions.type', $type);
    }
    public function scopeAccount($query, $accountId)
    {
        return $query->where('transactions.account_id', $accountId);
    }
    public function scopeStatus($query, $status)
    {
        return $query->where('transactions.status', $status);
    }
    public function scopeUser($query, $userId)
    {
        return $query->where('transactions.user_id', $userId);
    }
    public function scopeCategory($query, $categoryId)
    {
        return $query->where("category_id", $categoryId);
    }
}
