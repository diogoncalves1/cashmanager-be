<?php

namespace Modules\FinancialGoal\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialGoal\Entities\FinancialGoalUser;

class FinancialGoalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FinancialGoalUser::factory(3)->create();
    }
}
