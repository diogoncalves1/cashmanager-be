<?php

namespace Modules\Friends\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Friends\Entities\FriendshipRequest;
use Modules\Friends\Entities\FriendshipRequestModel;

class FriendshipRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FriendshipRequestModel::factory(3)->create();
    }
}
