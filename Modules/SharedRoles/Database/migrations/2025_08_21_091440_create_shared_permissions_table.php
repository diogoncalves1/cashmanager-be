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
        Schema::create('shared_permissions', function (Blueprint $table) {
            $table->id();
            $table->string("code", 191)->unique();
            $table->string("name", 191);
            $table->string("category", 191);
            $table->timestamps();
        });

        $permissions = [
            ['name' => 'Ver Permissões de Partilha', 'code' => 'viewSharedPermission', 'category' => 'Shared Permissions'],
            ['name' => 'Adicionar Permissões de Partilha', 'code' => 'createSharedPermission', 'category' => 'Shared Permissions'],
            ['name' => 'Editar Permissões de Partilha', 'code' => 'editSharedPermission', 'category' => 'Shared Permissions'],
            ['name' => 'Apagar Permissões de Partilha', 'code' => 'destroySharedPermission', 'category' => 'Shared Permissions'],
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
        Schema::dropIfExists('shared_permissions');
    }
};
