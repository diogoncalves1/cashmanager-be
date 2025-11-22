<?php

namespace Modules\Friends\Database\Seeders;

use Illuminate\Database\Seeder;

class FriendsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            FriendshipSeeder::class,
            FriendshipRequestSeeder::class
        ]);
    }
}
