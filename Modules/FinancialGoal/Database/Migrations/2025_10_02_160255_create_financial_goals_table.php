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
        Schema::create('financial_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('name')->nullable();
            $table->decimal('total_amount', 15);
            $table->decimal('contributed_amount', 15)->default(0);
            $table->date('start_date');
            $table->date('due_date');
            $table->enum('status', ['completed', 'in_progress', 'canceled'])->default('in_progress');
            $table->text('description')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        $permissions = [
            ['name' => 'Ver Meta Financeira', 'code' => 'viewFinancialGoal', 'category' => 'Metas Financeiras'],
            ['name' => 'Editar Meta Financeira', 'code' => 'updateFinancialGoal', 'category' => 'Metas Financeiras'],
            ['name' => 'Apagar Meta Financeira', 'code' => 'destroyFinancialGoal', 'category' => 'Metas Financeiras'],
            ['name' => 'Apagar Meta Financeira', 'code' => 'manageFinancialGoal', 'category' => 'Metas Financeiras'],
        ];

        foreach ($permissions as $permission) {
            $id               = DB::table('shared_permissions')->insertGetId($permission);
            $permissionRole[] = ['shared_permission_id' => $id, 'shared_role_id' => 1];
        }

        DB::table('shared_permission_roles')->insert($permissionRole);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_goals');

        $permissions = ['viewFinancialGoal', 'updateFinancialGoal', 'destroyFinancialGoal', "manageFinancialGoal"];

        foreach ($permissions as $permission) {
            DB::table('shared_permissions')->where('code', $permission)->delete();
        }
    }
};
