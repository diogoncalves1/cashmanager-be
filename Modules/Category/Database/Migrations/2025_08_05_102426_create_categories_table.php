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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->enum('type', ['revenue', 'expense']);
            $table->string('icon', 255)->nullable();
            $table->string('color', 255)->nullable();
            $table->boolean('default')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $permissions = [
            ['name' => 'Ver Categorias Predefinidas', 'code' => 'viewCategoryDefault', 'category' => 'Categorias'],
            ['name' => 'Adicionar Categoria Predefinida', 'code' => 'createCategoryDefault', 'category' => 'Categorias'],
            ['name' => 'Editar Categoria Predefinida', 'code' => 'editCategoryDefault', 'category' => 'Categorias'],
            ['name' => 'Apagar Categoria Predefinida', 'code' => 'destroyCategoryDefault', 'category' => 'Categorias'],
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
        Schema::dropIfExists('categories');
    }
};
