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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('type', ['cash', 'bank_account', 'credit_card', 'digital_wallet']);
            $table->decimal("balance", 15, 2)->default(0);
            $table->unsignedBigInteger("currency_id")->nullable();
            $table->boolean("active")->default(1);
            $table->timestamps();

            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        $permissions = [
            ['name' => 'Ver Conta', 'code' => 'viewAccount', 'category' => 'Contas'],
            ['name' => 'Editar Conta', 'code' => 'editAccount', 'category' => 'Contas'],
            ['name' => 'Apagar Conta', 'code' => 'destroyAccount', 'category' => 'Contas'],
            ['name' => 'Gerir Utilizadores da Conta', 'code' => 'manageAccountUsers', 'category' => 'Contas'],
            ['name' => 'Influenciar saldo de Utilizador', 'code' => 'updateUserBalance', 'category' => 'Contas'],
        ];

        foreach ($permissions as $permission) {
            $id                = DB::table('shared_permissions')->insertGetId($permission);
            $rolePermissions[] = ['shared_permission_id' => $id, 'shared_role_id' => 1];
        }

        DB::table('shared_permission_roles')->insert($rolePermissions);
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');

        $permissions = ['viewAccount', 'editAccount', 'destroyAccount', 'manageAccountUsers', 'updateUserBalance'];

        foreach ($permissions as $permission) {
            DB::table('shared_permissions')->where('code', $permission)->delete();
        }
    }
};
