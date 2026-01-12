<?php
namespace Modules\Accounts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;

// use Modules\Accounts\Database\Factories\AccountBasicViewFactory;

// TODO: Fazer commit de alterações: bugfix/ return only active accounts.

class AccountBasicView extends Account
{
    use HasFactory;

    protected $table = "account_basic_view";

    public function scopeActive($query, $active)
    {
        return $query->where('account_basic_view.active', $active);
    }
}
