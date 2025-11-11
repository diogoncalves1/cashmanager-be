<?php

namespace Modules\UserPreferences\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\UserPreferences\Entities\UserPrefence;

class UserPreferencesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserPrefence::factory(2)->create();
    }
}
