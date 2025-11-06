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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("code");
            $table->timestamps();
        });

        $roles = [
            ['name' => 'superAdmin', 'code' => 'superAdmin'],
            ['name' => 'admin', 'code' => 'admin'],

        ];
        DB::table('roles')->insert($roles);

        $permissions = [
            ['name' => 'Visualizar Perfil', 'code' => 'viewRole', 'category' => 'Perfis'],
            ['name' => 'Criar Perfil', 'code' => 'createRole', 'category' => 'Perfis'],
            ['name' => 'Editar Perfil', 'code' => 'editRole', 'category' => 'Perfis'],
            ['name' => 'Remover Perfil', 'code' => 'destroyRole', 'category' => 'Perfis'],
            ['name' => 'Gerir Perfil', 'code' => 'manageRole', 'category' => 'Perfis'],
        ];

        DB::table('permissions')->insert($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');

        $permissions = ['viewRole', 'createRole', 'editRole', 'destroyRole', 'manageRole',];

        foreach ($permissions as $permission)
            DB::table('permissions')->where('code', $permission)->delete();
    }
};
