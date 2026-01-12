<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Category\Entities\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_goal_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('financial_goal_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->enum('type', ['withdrawal', 'contribution']);
            $table->decimal('amount', 15);
            $table->date('date');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();

            $table->foreign('financial_goal_id')->references('id')->on('financial_goals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
        });

        $permissions = [
            ['name' => 'Ver Trasação Meta Financeira', 'code' => 'viewFinancialGoalTransaction', 'category' => 'Contribuicoes Metas Financeiras'],
            ['name' => 'Adicionar Trasações da Meta Financeira', 'code' => 'storeFinancialGoalTransaction', 'category' => 'Contribuicoes Metas Financeiras'],
            ['name' => 'Editar Trasações da Meta Financeira', 'code' => 'updateFinancialGoalTransaction', 'category' => 'Contribuicoes Metas Financeiras'],
            ['name' => 'Confirmar Trasações Agendadas', 'code' => 'confirmScheduledFinancialGoalTransactions', 'category' => 'Contribuicoes Metas Financeiras'],
            ['name' => 'Apagar Trasações da Meta Financeira', 'code' => 'destroyFinancialGoalTransaction', 'category' => 'Contribuicoes Metas Financeiras'],
        ];

        foreach ($permissions as $permission) {
            $id               = DB::table('shared_permissions')->insertGetId($permission);
            $permissionRole[] = ['shared_permission_id' => $id, 'shared_role_id' => 1];
        }

        DB::table('shared_permission_roles')->insert($permissionRole);

        $categories = [
            [
                'icon' => '', 'name'                          => ['en' => 'Financial goal contribution', 'pt' => 'Contribuíção da meta financeira'],
                'code' => 'financialGoalContribution', 'type' => 'expense', 'color' => '', 'is_default' => true,
            ],
            [
                'icon' => '', 'name'                        => ['en' => 'Financial goal withdrawal', 'pt' => 'Contribuíção da meta financeira'],
                'code' => 'financialGoalWithdrawal', 'type' => 'revenue', 'color' => '', 'is_default' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_goal_transactions');

        $permissions = ['viewFinancialGoalTransaction', 'storeFinancialGoalTransaction', 'updateFinancialGoalTransaction', 'confirmScheduledFinancialGoalTransactions', 'destroyFinancialGoalTransaction'];

        foreach ($permissions as $permission) {
            DB::table('shared_permissions')->where('code', $permission)->delete();
        }
    }
};
