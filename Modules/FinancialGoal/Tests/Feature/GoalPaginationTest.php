<?php

namespace Modules\FinancialGoal\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\FinancialGoal\Repositories\FinancialGoalRepository;
use Tests\Support\ListingTestCase;

class GoalPaginationTest extends ListingTestCase
{
    public function test_goal_list_preserves_reservation_totals_and_visibility(): void
    {
        Schema::create('financial_goals', function (Blueprint $t) {
            $t->id();
            $t->decimal('completed_funding')->nullable();
        });
        Schema::create('financial_goal_users', function (Blueprint $t) {
            $t->unsignedBigInteger('financial_goal_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('shared_role_id');
        });
        Schema::create('financial_goal_transactions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('financial_goal_id');
            $t->string('type');
            $t->string('status');
            $t->decimal('amount');
        });
        Schema::create('financial_goal_view', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('description')->default('');
            $t->string('status')->default('in_progress');
            $t->string('priority')->default('high');
            $t->decimal('totalAmount')->default(100);
            $t->decimal('contributedAmount')->default(40);
            $t->string('currencyCode')->default('EUR');
            $t->string('currencySymbol')->default('€');
        });
        DB::table('financial_goals')->insert([['id' => 1], ['id' => 2]]);
        DB::table('financial_goal_view')->insert([['id' => 1, 'name' => 'Trip'], ['id' => 2, 'name' => 'Trip private']]);
        DB::table('financial_goal_users')->insert([['financial_goal_id' => 1, 'user_id' => 1, 'shared_role_id' => 1], ['financial_goal_id' => 2, 'user_id' => 11, 'shared_role_id' => 1]]);
        DB::table('financial_goal_transactions')->insert([['financial_goal_id' => 1, 'type' => 'contribution', 'status' => 'completed', 'amount' => 40], ['financial_goal_id' => 1, 'type' => 'usage', 'status' => 'completed', 'amount' => 10]]);
        $this->mock(FinancialGoalRepository::class)->shouldReceive('getStats')->andReturn([]);
        $this->getJson('/api/v1/financial-goals?search=Trip&status[eq]=in_progress')->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.reservedAmount', 30)->assertJsonPath('data.0.usedAmount', 10);
    }
}
