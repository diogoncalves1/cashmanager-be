<?php
namespace Modules\Accounts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;

// use Modules\Accounts\Database\Factories\AccountBasicViewFactory;

class AccountBasicView extends Account
{
    use HasFactory;

    protected $table = "account_basic_view";
}
