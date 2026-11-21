<?php

namespace Modules\FinancialGoal\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\ListingTestCase;

class GoalTransactionPaginationTest extends ListingTestCase
{
    public function test_membership_is_checked_without_joining_the_aggregate_goal_view(): void
    {
        Schema::create('financial_goals', function (Blueprint $t) {
            $t->id();
        });
        Schema::create('financial_goal_users', function (Blueprint $t) {
            $t->unsignedBigInteger('financial_goal_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('shared_role_id');
        });
        Schema::create('financial_goal_transaction_view', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('financialGoalId');
            $t->string('description')->default('Saving');
            $t->date('date');
            $t->string('status')->default('completed');
            $t->string('type')->default('contribution');
            $t->decimal('amount')->default(10);
            $t->string('currencyCode')->default('EUR');
            $t->string('currencySymbol')->default('€');
        });
        DB::table('financial_goals')->insert([['id' => 1], ['id' => 2]]);
        DB::table('financial_goal_users')->insert([['financial_goal_id' => 1, 'user_id' => 1, 'shared_role_id' => 1], ['financial_goal_id' => 2, 'user_id' => 11, 'shared_role_id' => 1]]);
        DB::table('financial_goal_transaction_view')->insert([['id' => 1, 'financialGoalId' => 1, 'date' => '2026-11-15'], ['id' => 2, 'financialGoalId' => 2, 'date' => '2026-11-15']]);
        $this->getJson('/api/v1/financial-goal-transactions?dateFrom=2026-11-01&dateTo=2026-11-30&search=Saving')->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.id', 1)->assertJsonPath('data.0.actions.edit', false);
    }
}
