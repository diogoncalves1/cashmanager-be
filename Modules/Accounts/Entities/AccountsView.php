<?php
namespace Modules\Accounts\Entities;

class AccountsView extends Account
{
    protected $table = "accounts_view";
    protected $casts = [
        'balance' => 'float',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'id', 'id');
    }
    public function transactionsView()
    {
        return $this->hasMany(TransactionsView::class, 'accountId');
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    public function scopeType($query, $type)
    {
        return $query->where('accounts_view.type', $type);
    }
    public function scopeActive($query, $active)
    {
        return $query->where('accounts_view.status', $active);
    }
}
