<?php

namespace Modules\SharedRoles\Database\Seeders;

use Illuminate\Database\Seeder;

class SharedRolesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SharedRoleSeeder::class,
            SharedPermissionSeeder::class,
            SharedPermissionRoleSeeder::class
        ]);
    }
}
