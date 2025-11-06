<?php

namespace Modules\SharedRoles\Database\Seeders;

use Modules\SharedRoles\Entities\SharedPermission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SharedPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SharedPermission::factory(5)->create();
    }
}
