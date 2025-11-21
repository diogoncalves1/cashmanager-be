<?php

namespace Modules\Accounts\Database\Seeders;

use Modules\Accounts\Entities\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Account::factory(5)->create();
    }
}
