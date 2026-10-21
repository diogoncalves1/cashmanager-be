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
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->enum('type', ['expense', 'revenue']);
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();

            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly', 'custom']);
            $table->integer('custom_frequency_days')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->boolean("is_linked")->default(false);
            $table->enum('linked_type', ["Modules\\Debts\\Entities\\Debt", "Modules\\FinancialGoal\\Entities\\FinancialGoal"])->nullable();
            $table->bigInteger('linked_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        $permissions = [
            ['name' => 'Editar Transação Recorrente', 'code' => 'editRecurringTransaction', 'category' => 'Transações Recorrentes'],
            ['name' => 'Apagar Transação Recorrente', 'code' => 'destroyRecurringTransaction', 'category' => 'Transações Recorrentes'],
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
        Schema::dropIfExists('recurring_transactions');

        $permissions = ['viewAccount', 'editAccount', 'destroyAccount', 'manageAccountUsers', 'updateUserBalance'];

        foreach ($permissions as $permission) {
            DB::table('shared_permissions')->where('code', $permission)->delete();
        }
    }
};
