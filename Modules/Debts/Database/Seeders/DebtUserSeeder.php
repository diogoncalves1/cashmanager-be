<?php
namespace Modules\Debts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Debts\Entities\DebtUser;

class DebtUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DebtUser::factory(4)->create();
    }
}
