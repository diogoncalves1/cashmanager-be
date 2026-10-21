<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('broadcast_notifications', function (Blueprint $table) {
            $table->id();

            $table->string('type_code');
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->boolean('send_email')->default(0);

            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('type_code')->references('code')->on('notification_types')->onDelete('cascade');

            $table->timestamps();
        });

        $permissions = [
            ['name' => 'Ver Notificações', 'code' => 'viewNotifications', 'category' => 'Notificações'],
            ['name' => 'Ver Email de Notificações', 'code' => 'viewNotificationMail', 'category' => 'Notificações'],
            ['name' => 'Criar Notificações', 'code' => 'createNotifications', 'category' => 'Notificações'],
            ['name' => 'Editar Notificações', 'code' => 'editNotifications', 'category' => 'Notificações'],
            ['name' => 'Apagar Notificações', 'code' => 'destroyNotifications', 'category' => 'Notificações'],
        ];

        DB::table('permissions')->insert($permissions);

        foreach ($permissions as $permission) {
            $id                = DB::table('permissions')->insertGetId($permission);
            $rolePermissions[] = ['permission_id' => $id, 'role_id' => 1];
        }

        DB::table('role_permissions')->insert($rolePermissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcast_notifications');

        $permissions = ['viewUser', 'createUser', 'editUser', 'destroyUser'];

        foreach ($permissions as $permission) {
            DB::table('permissions')->where('code', $permission)->delete();
        }
    }
};
