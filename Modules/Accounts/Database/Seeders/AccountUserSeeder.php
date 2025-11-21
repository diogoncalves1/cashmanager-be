<?php

namespace Modules\Accounts\Database\Seeders;

use Modules\Accounts\Entities\AccountUser;
use Illuminate\Database\Seeder;

class AccountUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AccountUser::factory(4)->create();
    }
}
