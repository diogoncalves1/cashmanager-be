<?php
namespace Modules\Accounts\Database\Seeders;

use Illuminate\Database\Seeder;

class AccountsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            AccountSeeder::class,
            AccountUserSeeder::class,
            AccountUserInviteSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
