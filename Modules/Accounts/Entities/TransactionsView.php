<?php
namespace Modules\Accounts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Category\Casts\CategoryNameCast;
use Modules\Category\Entities\Category;

// use Modules\Accounts\Database\Factories\TransactionsViewFactory;

class TransactionsView extends Transaction
{
    use HasFactory;

    protected $table = "transactions_view";
    protected $casts = [
        'amount'       => 'float',
        'categoryName' => CategoryNameCast::class,
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryId');
    }

    public function scopeType($query, $type)
    {
        return $query->where("transactions_view.type", $type);
    }
    public function scopeStatus($query, $status)
    {
        return $query->where("transactions_view.status", $status);
    }
    public function scopeAccount($query, $accountId)
    {
        return $query->where("transactions_view.accountId", $accountId);
    }
    public function scopeCategory($query, $categoryId)
    {
        return $query->where("transactions_view.categoryId", $categoryId);
    }
    public function scopeUser($query, $userId)
    {
        return $query->where("transactions_view.userId", $userId);
    }
}
