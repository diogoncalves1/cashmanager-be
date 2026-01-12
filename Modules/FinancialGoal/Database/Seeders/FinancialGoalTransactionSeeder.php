<?php
namespace Modules\FinancialGoal\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;

class FinancialGoalTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FinancialGoalTransaction::factory(3)->create();
    }
}
