<?php

namespace Modules\User\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\ListingTestCase;

class UserSearchPaginationTest extends ListingTestCase
{
    public function test_user_search_never_serializes_private_attributes(): void
    {
        foreach (['friendships', 'friendship_requests'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('sender_id');
                $t->unsignedBigInteger('receiver_id');
                $t->string('status');
            });
        }
        DB::table('users')->where('id', 2)->update(['email' => 'secret@example.test', 'password' => 'secret']);
        $this->getJson('/api/v1/users/search?search=Other')->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.name', 'Other')
            ->assertJsonMissingPath('data.0.email')->assertJsonMissingPath('data.0.password')->assertJsonMissingPath('data.0.email_verified_at');
    }
}
