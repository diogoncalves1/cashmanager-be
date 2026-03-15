<?php
namespace Modules\FinancialGoal\Database\Seeders;

use Illuminate\Database\Seeder;

class FinancialGoalDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            FinancialGoalSeeder::class,
            FinancialGoalUserSeeder::class,
            FinancialGoalTransactionSeeder::class,
            FinancialGoalUserInviteSeeder::class,
        ]);
    }
}
