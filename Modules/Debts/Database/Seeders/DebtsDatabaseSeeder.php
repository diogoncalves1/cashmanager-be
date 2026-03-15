<?php
namespace Modules\Debts\Database\Seeders;

use Illuminate\Database\Seeder;

class DebtsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DebtSeeder::class,
            DebtPaymentSeeder::class,
            DebtUserSeeder::class,
            DebtUserInviteSeeder::class,
        ]);
    }
}
