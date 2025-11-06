<?php

namespace Modules\SharedRoles\Database\Seeders;

use Modules\SharedRoles\Entities\SharedRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SharedRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SharedRole::factory(5)->create();
    }
}
