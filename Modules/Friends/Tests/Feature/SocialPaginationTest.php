<?php

namespace Modules\Friends\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\ListingTestCase;

class SocialPaginationTest extends ListingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['friendships', 'friendship_requests'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('sender_id');
                $t->unsignedBigInteger('receiver_id');
                $t->string('status');
                $t->timestamps();
            });
        }
    }

    public function test_peer_search_cannot_match_the_readers_own_name_or_unrelated_friendships(): void
    {
        DB::table('friendships')->insert([['id' => 1, 'sender_id' => 1, 'receiver_id' => 2, 'status' => 'friend'], ['id' => 2, 'sender_id' => 2, 'receiver_id' => 11, 'status' => 'friend']]);
        $this->getJson('/api/v1/friendships?search=Other')->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.user.name', 'Other')->assertJsonMissingPath('data.0.user.email');
        $this->getJson('/api/v1/friendships?search=Reader')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/friendships?search=Private')->assertOk()->assertJsonCount(0, 'data');
    }
}
