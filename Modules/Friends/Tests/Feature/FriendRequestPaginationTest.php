<?php

namespace Modules\Friends\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\ListingTestCase;

class FriendRequestPaginationTest extends ListingTestCase
{
    public function test_sent_and_received_request_actions_match_the_direction(): void
    {
        Schema::create('friendship_requests', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('sender_id');
            $t->unsignedBigInteger('receiver_id');
            $t->string('status')->default('pending');
            $t->timestamps();
        });
        DB::table('friendship_requests')->insert([['id' => 1, 'sender_id' => 1, 'receiver_id' => 2, 'created_at' => '2026-11-10'], ['id' => 2, 'sender_id' => 2, 'receiver_id' => 1, 'created_at' => '2026-11-10'], ['id' => 3, 'sender_id' => 2, 'receiver_id' => 11, 'created_at' => '2026-11-10']]);
        $this->getJson('/api/v1/friendship-requests/sent?search=Other')->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.actions.accept', false)->assertJsonPath('data.0.actions.cancel', true);
        $this->getJson('/api/v1/friendship-requests/received?search=Other')->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.actions.accept', true)->assertJsonPath('data.0.actions.cancel', false);
    }
}
