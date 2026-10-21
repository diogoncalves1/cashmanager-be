<?php
namespace Modules\RecurringTransaction\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\RecurringTransaction\Entities\RecurringTransaction;

class RecurringTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RecurringTransaction::factory(4)->create();
    }
}
