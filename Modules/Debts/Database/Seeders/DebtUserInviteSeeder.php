<?php
namespace Modules\Debts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Debts\Entities\DebtUserInvite;

class DebtUserInviteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DebtUserInvite::factory(3)->create();
    }
}
