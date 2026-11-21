<?php

namespace Modules\ActivityLog\Tests\Feature;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Accounts\Entities\AccountsView;
use Modules\Accounts\Repositories\AccountRepository;
use Tests\Support\ListingTestCase;

class ActivityPaginationTest extends ListingTestCase
{
    public function test_activity_is_scoped_to_the_authorized_subject_and_type(): void
    {
        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('subject_id');
            $t->unsignedBigInteger('user_id');
            $t->string('type');
            $t->json('metadata');
            $t->timestamps();
        });
        DB::table('activity_logs')->insert([
            ['id' => 1, 'subject_id' => 1, 'user_id' => 1, 'type' => 'account', 'metadata' => '{"type":"account_created","accountName":"Main"}', 'created_at' => '2026-11-10'],
            ['id' => 2, 'subject_id' => 2, 'user_id' => 1, 'type' => 'account', 'metadata' => '{"type":"account_created","accountName":"Private"}', 'created_at' => '2026-11-10'],
            ['id' => 3, 'subject_id' => 1, 'user_id' => 1, 'type' => 'debt', 'metadata' => '{"type":"debt_created"}', 'created_at' => '2026-11-10'],
        ]);
        $this->mock(AccountRepository::class)->shouldReceive('showToUser')->once()->withArgs(fn ($request, $id) => $id === '1')->andReturn(new AccountsView);
        $this->getJson('/api/v1/accounts/1/activity?search=Reader')->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.user.name', 'Reader')->assertJsonMissingPath('data.0.metadata');
    }

    public function test_an_unauthorized_activity_request_remains_403(): void
    {
        $this->mock(AccountRepository::class)->shouldReceive('showToUser')->andThrow(new AuthorizationException);
        $this->getJson('/api/v1/accounts/2/activity')->assertForbidden();
    }
}
