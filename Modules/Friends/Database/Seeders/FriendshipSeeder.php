<?php

namespace Modules\Friends\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Friends\Entities\Friendship;

class FriendshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Friendship::factory(3)->create();
    }
}
