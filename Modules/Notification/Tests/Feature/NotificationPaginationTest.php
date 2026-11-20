<?php

namespace Modules\Notification\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Notification\Actions\NotificationType\RenderNotificationTemplateAction;
use Tests\Support\ListingTestCase;

class NotificationPaginationTest extends ListingTestCase
{
    public function test_union_uses_matching_columns_and_counts_personal_and_broadcast_rows_once(): void
    {
        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->json('data')->nullable();
            $t->string('type_code');
            $t->timestamp('read_at')->nullable();
            $t->timestamp('archived_at')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('broadcast_notifications', function (Blueprint $t) {
            $t->id();
            $t->string('type_code');
            $t->timestamps();
        });
        Schema::create('notification_types', function (Blueprint $t) {
            $t->id();
            $t->string('code');
            $t->string('title')->default('Alert');
            $t->string('message')->default('Message');
        });
        Schema::create('notification_keywords', function (Blueprint $t) {
            $t->id();
        });
        Schema::create('notification_type_keywords', function (Blueprint $t) {
            $t->unsignedBigInteger('notification_type_id');
            $t->unsignedBigInteger('notification_keyword_id');
        });
        DB::table('notification_types')->insert(['id' => 1, 'code' => 'alert']);
        DB::table('notifications')->insert([
            ['id' => 1, 'user_id' => 1, 'type_code' => 'alert', 'created_at' => '2026-11-15', 'archived_at' => null, 'deleted_at' => null],
            ['id' => 2, 'user_id' => 11, 'type_code' => 'alert', 'created_at' => '2026-11-15', 'archived_at' => null, 'deleted_at' => null],
            ['id' => 3, 'user_id' => 1, 'type_code' => 'alert', 'created_at' => '2026-11-15', 'archived_at' => '2026-11-16', 'deleted_at' => null],
            ['id' => 4, 'user_id' => 1, 'type_code' => 'alert', 'created_at' => '2026-11-15', 'archived_at' => null, 'deleted_at' => '2026-11-16'],
        ]);
        DB::table('broadcast_notifications')->insert(['id' => 1, 'type_code' => 'alert', 'created_at' => '2026-11-15']);
        $this->mock(RenderNotificationTemplateAction::class)->shouldReceive('execute')->andReturn(['title' => 'Alert', 'message' => 'Message', 'pathname' => '/']);
        $first = $this->getJson('/api/v1/notifications?per_page=1&search=Alert')->assertOk()->assertJsonPath('pagination.total', 2)->assertJsonCount(1, 'data');
        $second = $this->getJson('/api/v1/notifications?per_page=1&search=Alert&page=2')->assertOk()->assertJsonCount(1, 'data');
        $this->assertNotSame($first->json('data.0.source'), $second->json('data.0.source'));
    }
}
