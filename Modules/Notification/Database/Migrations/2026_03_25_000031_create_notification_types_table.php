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
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->json('title');
            $table->json('message');
            $table->json('mail_subject')->nullable();
            $table->json('mail_message')->nullable();
            $table->json('mail_signature')->nullable();
            $table->boolean('is_broadcast')->default(0);
            $table->string('pathname')->nullable();

            $table->timestamps();
        });

        $notificationTypes = [
            [
                'code'           => 'test',
                'title'          => json_encode([
                    'pt' => 'Teste',
                    'en' => 'Test',
                ]),
                'message'        => json_encode([
                    'pt' => 'Esta é uma notificação de teste.',
                    'en' => 'This is a test notification.',
                ]),
                'mail_subject'   => json_encode([
                    'pt' => 'Notificação de Teste',
                    'en' => 'Test Notification',
                ]),
                'mail_message'   => json_encode([
                    'pt' => '<p>Notificação de Teste</p><p>Valor: [$amount]</p><p>Descrição: [$description]</p>',
                    'en' => '<p>Test Notification</p><p>Amount: [$amount]</p><p>Description: [$description]</p>',
                ]),
                'mail_signature' => json_encode([
                    'pt' => '<p>Notificação de Teste</p><p>Valor: [$amount]</p><p>Descrição: [$description]</p>',
                    'en' => '<p>Test Notification</p><p>Amount: [$amount]</p><p>Description: [$description]</p>',
                ]),
                'pathname'       => route('admin.index'),
            ],
        ];

        DB::table('notification_types')->insert($notificationTypes);

        $permissions = [
            ['name' => 'Ver Tipos de Notificações', 'code' => 'viewNotificationTypes', 'category' => 'Tipos de Notificações'],
            ['name' => 'Criar Tipos de Notificações', 'code' => 'createNotificationTypes', 'category' => 'Tipos de Notificações'],
            ['name' => 'Editar Tipos de Notificações', 'code' => 'editNotificationTypes', 'category' => 'Tipos de Notificações'],
            ['name' => 'Apagar Tipos de Notificações', 'code' => 'destroyNotificationTypes', 'category' => 'Tipos de Notificações'],
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
        Schema::dropIfExists('notification_types');

        $permissions = ['viewNotificationTypes', 'createNotificationTypes', 'editNotificationTypes', 'destroyNotificationTypes', 'manageNotificationKeywords'];

        foreach ($permissions as $permission) {
            DB::table('permissions')->where('code', $permission)->delete();
        }
    }
};
