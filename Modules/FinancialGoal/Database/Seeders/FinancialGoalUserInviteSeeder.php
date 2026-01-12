<?php
namespace Modules\FinancialGoal\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FinancialGoal\Entities\FinancialGoalUserInvite;

class FinancialGoalUserInviteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FinancialGoalUserInvite::factory(3)->create();
    }
}
