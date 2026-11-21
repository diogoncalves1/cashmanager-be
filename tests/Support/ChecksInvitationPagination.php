<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ChecksInvitationPagination
{
    protected function checkInvitationPagination(string $module, string $table, string $foreign, string $subjectTable): void
    {
        if (! Schema::hasTable($subjectTable)) {
            Schema::create($subjectTable, function (Blueprint $t) {
                $t->id();
                $t->string('name');
            });
            DB::table($subjectTable)->insert(['id' => 1, 'name' => 'Subject']);
        }
        Schema::create($table, function (Blueprint $t) use ($foreign) {
            $t->id();
            $t->unsignedBigInteger($foreign);
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('invited_by_id');
            $t->unsignedBigInteger('shared_role_id')->default(1);
            $t->string('status')->default('pending');
            $t->timestamps();
        });
        DB::table($table)->insert([
            ['id' => 1, $foreign => 1, 'user_id' => 1, 'invited_by_id' => 2, 'created_at' => '2026-11-10'],
            ['id' => 2, $foreign => 1, 'user_id' => 2, 'invited_by_id' => 1, 'created_at' => '2026-11-10'],
            ['id' => 3, $foreign => 1, 'user_id' => 11, 'invited_by_id' => 2, 'created_at' => '2026-11-10'],
        ]);
        $this->getJson('/api/v1/'.$module.'/received-invitations?status[eq]=pending&search=Other')->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.id', 1)->assertJsonPath('data.0.sender.name', 'Other');
        $this->getJson('/api/v1/'.$module.'/sent-invitations?per_page=1')->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.id', 2);
    }
}
