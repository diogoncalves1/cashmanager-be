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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('account_id');
            $table->enum('type', ['expense', 'revenue']);
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('description')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });

        $permissions = [
            ['name' => 'Ver Transações', 'code' => 'viewTransaction', 'category' => 'Transações'],
            ['name' => 'Adicionar Transação', 'code' => 'createTransaction', 'category' => 'Transações'],
            ['name' => 'Editar Transação', 'code' => 'editTransaction', 'category' => 'Transações'],
            ['name' => 'Apagar Transação', 'code' => 'destroyTransaction', 'category' => 'Transações'],
            ['name' => 'Confirmar Transação', 'code' => 'confirmTransaction', 'category' => 'Transações'],
        ];

        foreach ($permissions as $permission) {
            $id = DB::table('shared_permissions')->insertGetId($permission);
            $rolePermissions[] = ['shared_permission_id' => $id, 'shared_role_id' => 1];
        }

        DB::table('shared_permission_roles')->insert($rolePermissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        
        $permissions = ['viewTransaction', 'createTransaction', 'editTransaction', 'destroyTransaction', 'confirmTransaction'];

        foreach ($permissions as $permission) {
            DB::table('shared_permissions')->where('code', $permission)->delete();
        }
    }
};
