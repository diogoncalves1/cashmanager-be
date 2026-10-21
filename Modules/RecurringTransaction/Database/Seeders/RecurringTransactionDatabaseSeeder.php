<?php
namespace Modules\RecurringTransaction\Database\Seeders;

use Illuminate\Database\Seeder;

class RecurringTransactionDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RecurringTransactionSeeder::class,
        ]);
    }
}
