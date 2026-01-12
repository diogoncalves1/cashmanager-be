<?php

namespace Modules\FinancialGoal\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialGoal\Entities\FinancialGoal;

class FinancialGoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FinancialGoal::factory(3)->create();
    }
}
