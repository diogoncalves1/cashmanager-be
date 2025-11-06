<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\SharedRoles\Entities\SharedRole;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shared_roles', function (Blueprint $table) {
            $table->id();
            $table->string("code", 191)->unique();
            $table->json("name");
            $table->tinyInteger('visible')->default(1);
            $table->timestamps();
        });

        SharedRole::create([
            'code' => 'creator',
            'name' => [
                'en' => 'Creator',
                'pt' => 'Criador',
            ],
            'visible' => 0
        ]);

        $permissions = [
            ['name' => 'Ver Papéis de Partilha', 'code' => 'viewSharedRole', 'category' => 'Shared Roles'],
            ['name' => 'Adicionar Papel de Partilha', 'code' => 'createSharedRole', 'category' => 'Shared Roles'],
            ['name' => 'Editar Papel de Partilha', 'code' => 'editSharedRole', 'category' => 'Shared Roles'],
            ['name' => 'Apagar Papel de Partilha', 'code' => 'destroySharedRole', 'category' => 'Shared Roles'],
            ['name' => 'Gerir Permissões do Papel de Partilha', 'code' => 'manageSharedRolePermission', 'category' => 'Shared Roles'],
        ];

        foreach ($permissions as $permission) {
            $id = DB::table('permissions')->insertGetId($permission);
            $rolePermissions[] = ['permission_id' => $id, 'role_id' => 1];
        }

        DB::table('role_permissions')->insert($rolePermissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_roles');
    }
};
