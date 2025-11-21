<?php

namespace Modules\Accounts\Database\Seeders;

use Modules\Accounts\Entities\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaction::factory(4)->create();
    }
}
