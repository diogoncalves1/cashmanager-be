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
        Schema::create('financial_goal_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('financial_goal_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shared_role_id')->nullable();

            $table->timestamps();

            $table->foreign('financial_goal_id')->references('id')->on('financial_goals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shared_role_id')->references('id')->on('shared_roles')->onDelete('set null');
        });

        $permissions = [
            ['name' => 'Gerir Utilizadores da Meta Financeira', 'code' => 'manageFinancialGoalUsers', 'category' => 'Contribuicoes Metas Financeiras'],
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
        Schema::dropIfExists('financial_goal_users');

        $permissions = ['manageFinancialGoalUsers'];

        foreach ($permissions as $permission) {
            DB::table('shared_permissions')->where('code', $permission)->delete();
        }
    }
};
