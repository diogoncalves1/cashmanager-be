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
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('name');
            $table->decimal('total_amount', 15);
            $table->decimal('paid_amount', 15)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->integer('months')->nullable();
            $table->decimal('interest_rate', 8, 5)->default(0);
            $table->date('start_date');
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->integer("months_paid")->default(0);
            $table->text('description')->nullable();
            $table->decimal('monthly_amount', 15, 2)->nullable();
            $table->timestamps();

            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        $permissions = [
            ['name' => 'Ver Dívida', 'code' => 'viewDebt', 'category' => 'Dívidas'],
            ['name' => 'Editar Dívida', 'code' => 'editDebt', 'category' => 'Dívidas'],
            ['name' => 'Apagar Dívida', 'code' => 'destroyDebt', 'category' => 'Dívidas'],
            ['name' => 'Gerir Utilizadores da Dívida', 'code' => 'manageDebtUsers', 'category' => 'Dívidas'],
        ];

        foreach ($permissions as $permission) {
            $id                = DB::table('shared_permissions')->insertGetId($permission);
            $rolePermissions[] = ['shared_permission_id' => $id, 'shared_role_id' => 1];
        }

        DB::table('shared_permission_roles')->insert($rolePermissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');

        $permissions = ['viewDebt', 'editDebt', 'destroyDebt', 'manageDebtUsers'];

        foreach ($permissions as $permission) {
            DB::table("shared_permissions")->where("code", $permission)->delete();
        }
    }
};
