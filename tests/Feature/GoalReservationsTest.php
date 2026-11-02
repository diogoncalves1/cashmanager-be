<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\Transaction;
use Modules\Accounts\Repositories\TransactionRepository;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\FinancialGoal\Repositories\FinancialGoalRepository;
use Modules\FinancialGoal\Services\MigrateGoalReservations;
use Modules\FinancialGoal\Services\GoalReservations;
use Modules\User\Entities\User;
use Tests\TestCase;

class GoalReservationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->travelTo(now()->setDate(2026, 11, 1)->setTime(12, 0));
        app()->setLocale('en');
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
        });
        Schema::create('currencies', function (Blueprint $t) {
            $t->id();
            $t->string('code');
            $t->string('symbol');
        });
        Schema::create('accounts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type')->default('bank_account');
            $t->boolean('active')->default(true);
            $t->unsignedBigInteger('currency_id');
            $t->decimal('balance', 15, 2);
            $t->timestamps();
        });
        Schema::create('shared_roles', function (Blueprint $t) {
            $t->id();
            $t->string('code');
            $t->json('name');
        });
        Schema::create('shared_permissions', function (Blueprint $t) {
            $t->id();
            $t->string('code');
        });
        Schema::create('shared_permission_roles', function (Blueprint $t) {
            $t->unsignedBigInteger('shared_role_id');
            $t->unsignedBigInteger('shared_permission_id');
        });
        foreach (['account_users' => 'account_id', 'financial_goal_users' => 'financial_goal_id'] as $table => $column) {
            Schema::create($table, function (Blueprint $t) use ($column) {
                $t->id();
                $t->unsignedBigInteger($column);
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('shared_role_id');
            });
        }
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->text('name');
            $t->string('type');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('code')->nullable();
            $t->boolean('is_default')->default(false);
            $t->timestamp('archived_at')->nullable();
            $t->timestamps();
        });
        Schema::create('transactions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('account_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('category_id')->nullable();
            $t->unsignedBigInteger('recurring_transaction_id')->nullable();
            $t->string('type');
            $t->decimal('amount', 15, 2);
            $t->date('date');
            $t->string('status');
            $t->text('description')->nullable();
            $t->timestamps();
        });
        Schema::create('financial_goals', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->unsignedBigInteger('currency_id');
            $t->decimal('total_amount', 15, 2);
            $t->decimal('contributed_amount', 15, 2)->default(0);
            $t->string('status')->default('in_progress');
            $t->date('start_date')->nullable();
            $t->date('due_date')->nullable();
            $t->text('description')->nullable();
            $t->string('priority')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamp('canceled_at')->nullable();
            $t->timestamps();
        });
        Schema::create('financial_goal_transactions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('financial_goal_id');
            $t->unsignedBigInteger('user_id');
            $t->foreignId('transaction_id')->nullable()->constrained('transactions')->cascadeOnDelete();
            $t->string('type');
            $t->decimal('amount', 15, 2);
            $t->date('date');
            $t->string('status');
            $t->text('description')->nullable();
            $t->timestamps();
        });
        $this->migration()->up();
        DB::table('users')->insert([['id' => 1, 'name' => 'Owner'], ['id' => 2, 'name' => 'Other']]);
        DB::table('currencies')->insert([['id' => 1, 'code' => 'EUR', 'symbol' => '€'], ['id' => 2, 'code' => 'USD', 'symbol' => '$']]);
        DB::table('shared_roles')->insert(['id' => 1, 'code' => 'creator', 'name' => '{"en":"Owner","pt":"Criador"}']);
        foreach (['viewTransaction', 'viewAccount', 'createTransaction', 'editTransaction', 'destroyTransaction', 'viewFinancialGoal', 'updateFinancialGoal', 'destroyFinancialGoal', 'storeFinancialGoalTransaction', 'updateFinancialGoalTransaction', 'destroyFinancialGoalTransaction', 'confirmScheduledFinancialGoalTransactions'] as $permission) {
            $id = DB::table('shared_permissions')->insertGetId(['code' => $permission]);
            DB::table('shared_permission_roles')->insert(['shared_role_id' => 1, 'shared_permission_id' => $id]);
        }
        Account::create(['id' => 1, 'name' => 'Bank', 'balance' => 3000, 'currency_id' => 1, 'active' => true]);
        DB::table('account_users')->insert(['account_id' => 1, 'user_id' => 1, 'shared_role_id' => 1]);
        DB::table('categories')->insert(['id' => 1, 'name' => 'Car', 'type' => 'expense', 'user_id' => 1]);
        $goal = FinancialGoal::create(['name' => 'First Car', 'total_amount' => 2000, 'currency_id' => 1, 'funding_mode' => 'reservation']);
        DB::table('financial_goal_users')->insert(['financial_goal_id' => $goal->id, 'user_id' => 1, 'shared_role_id' => 1]);
        $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Owner'])->setRelation('preferences', null));
        $this->mock(ActivityLogRepository::class, fn ($mock) => $mock->shouldReceive('storeActivity')->andReturnNull());
    }

    private function migration()
    {
        return require base_path('Modules/FinancialGoal/Database/Migrations/2026_11_02_210000_prepare_goal_reservation_rollout.php');
    }

    private function request(array $values = []): Request
    {
        $request = new Request(array_replace(['financial_goal_id' => 1, 'account_id' => 1, 'amount' => 2000,
            'request_key' => (string) Str::uuid(), 'type' => 'contribution', 'date' => today()->toDateString(), 'status' => 'completed'], $values));
        $request->setUserResolver(fn () => auth()->user());

        return $request;
    }

    private function add(array $values = []): FinancialGoalTransaction
    {
        return app(GoalReservations::class)->store($this->request($values));
    }

    public function test_first_car_reserve_purchase_complete_and_repeat_do_not_duplicate_money(): void
    {
        $this->add(['request_key' => 'reserve-1']);
        $this->add(['request_key' => 'reserve-1']);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertEquals(1000, GoalReservations::accountSummary(Account::find(1))['availableAmount']);
        $this->assertDatabaseCount('transactions', 0);
        $purchase = $this->add(['type' => 'usage', 'category_id' => 1, 'request_key' => 'purchase-1']);
        $retry = $this->add(['type' => 'usage', 'category_id' => 1, 'request_key' => 'purchase-1']);
        $this->assertSame($purchase->id, $retry->id);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertEquals(1000, Account::find(1)->balance);
        $this->assertEquals(0, GoalReservations::reserved(1, 1));
        $this->assertEquals(1000, GoalReservations::accountSummary(Account::find(1))['availableAmount']);
        $goal = app(FinancialGoalRepository::class)->complete($this->request(), '1');
        $this->assertEquals('completed', $goal->status);
        $this->assertEquals(2000, $goal->completed_funding);
        $this->assertEquals(2000, GoalReservations::goalSummary($goal)['usedAmount']);
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_linking_an_existing_expense_does_not_debit_again_and_allocations_are_bounded(): void
    {
        $this->add();
        $expense = app(TransactionRepository::class)->store($this->request(['type' => 'expense', 'category_id' => 1]));
        $this->add(['type' => 'usage', 'transaction_id' => $expense->id, 'amount' => 1500]);
        $this->assertEquals(1000, Account::find(1)->balance);
        $this->assertEquals(500, GoalReservations::reserved(1, 1));
        $this->add(['type' => 'contribution', 'amount' => 500]);
        $this->add(['type' => 'usage', 'transaction_id' => $expense->id, 'amount' => 500]);
        $this->assertEquals(1000, Account::find(1)->balance);
        $this->assertDatabaseCount('transactions', 1);
        try {
            $this->add(['type' => 'usage', 'transaction_id' => $expense->id, 'amount' => 1]);
            $this->fail('Cannot allocate the expense twice');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('amount', $e->errors());
        }
        $this->assertDatabaseCount('financial_goal_transactions', 4);
    }

    public function test_partial_purchase_leftovers_and_completed_history_are_distinct(): void
    {
        $this->add();
        $this->add(['type' => 'usage', 'amount' => 1800, 'category_id' => 1]);
        app(FinancialGoalRepository::class)->complete($this->request(), '1');
        $this->add(['type' => 'withdrawal', 'amount' => 200]);
        $goal = FinancialGoal::find(1);
        $this->assertEquals(0, GoalReservations::reserved(1, 1));
        $this->assertEquals(1800, $goal->contributed_amount);
        $this->assertEquals(2000, $goal->completed_funding);
        $this->assertEquals(1200, GoalReservations::accountSummary(Account::find(1))['availableAmount']);
        $this->assertEquals('completed', $goal->status);
    }

    public function test_pending_reservations_are_only_effective_on_confirmation_and_confirm_is_idempotent(): void
    {
        $entry = $this->add(['status' => 'pending', 'date' => today()->addDay()->toDateString(), 'amount' => '12.34']);
        $this->assertEquals(0, GoalReservations::reserved(1));
        try {
            app(GoalReservations::class)->change($this->request(), (string) $entry->id, 'confirm');
            $this->fail();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('date', $e->errors());
        }
        $this->travel(2)->days();
        app(GoalReservations::class)->change($this->request(), (string) $entry->id, 'confirm');
        app(GoalReservations::class)->change($this->request(), (string) $entry->id, 'confirm');
        $this->assertEquals(12.34, GoalReservations::reserved(1));
        $this->assertEquals(12.34, FinancialGoal::find(1)->contributed_amount);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_reserve_limits_currency_permissions_and_categories_roll_back(): void
    {
        $this->add();
        foreach ([['type' => 'usage', 'amount' => 2001, 'category_id' => 1], ['type' => 'withdrawal', 'amount' => 2001], ['type' => 'usage', 'category_id' => 999]] as $values) {
            try {
                $this->add($values);
                $this->fail('Expected validation');
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
        $this->assertDatabaseCount('financial_goal_transactions', 1);
        $this->assertDatabaseCount('transactions', 0);
        $this->assertEquals(3000, Account::find(1)->balance);
        Account::whereKey(1)->update(['currency_id' => 2]);
        $this->postJson('/api/v1/financial-goal-transactions', $this->request()->all())->assertUnprocessable()->assertJsonValidationErrors('account_id');
        Account::whereKey(1)->update(['currency_id' => 1]);
        $this->actingAs((new User)->forceFill(['id' => 2, 'name' => 'Other']));
        $this->postJson('/api/v1/financial-goal-transactions', $this->request()->all())->assertForbidden();
        $this->getJson('/api/v1/financial-goals/1/reservations')->assertForbidden();
    }

    public function test_completed_reservations_are_immutable_and_usage_unlink_preserves_expense(): void
    {
        $reserve = $this->add();
        $this->deleteJson('/api/v1/financial-goal-transactions/'.$reserve->id)->assertUnprocessable();
        $usage = $this->add(['type' => 'usage', 'category_id' => 1]);
        try {
            app(TransactionRepository::class)->destroy($this->request(), (string) $usage->transaction_id);
            $this->fail();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('transaction_id', $e->errors());
        }
        app(GoalReservations::class)->change($this->request(), (string) $usage->id, 'destroy');
        $this->assertDatabaseCount('transactions', 1);
        $this->assertEquals(1000, Account::find(1)->balance);
        $this->assertEquals(2000, GoalReservations::reserved(1));
        $this->assertEquals(1000, GoalReservations::accountSummary(Account::find(1))['reservationShortfall']);
    }

    public function test_http_validation_no_accept_and_pending_state_return_field_errors(): void
    {
        $this->post('/api/v1/financial-goal-transactions', $this->request(['amount' => '1.234'])->all())->assertUnprocessable()->assertJsonValidationErrors('amount');
        $this->postJson('/api/v1/financial-goal-transactions', $this->request(['type' => 'usage', 'status' => 'pending'])->all())->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->postJson('/api/v1/financial-goal-transactions', $this->request(['amount' => 50, 'request_key' => 'test-key'])->all())->assertOk();
        $this->postJson('/api/v1/financial-goal-transactions', $this->request(['amount' => 51, 'request_key' => 'test-key'])->all())->assertUnprocessable()->assertJsonValidationErrors('request_key');
        $this->assertDatabaseCount('financial_goal_transactions', 1);
    }

    public function test_context_and_candidate_expenses_are_scoped_and_paginated(): void
    {
        $this->add();
        for ($i = 0; $i < 23; $i++) {
            Transaction::create(['account_id' => 1, 'user_id' => 1, 'category_id' => 1, 'type' => 'expense', 'amount' => 10, 'date' => today()->toDateString(), 'status' => 'completed']);
        }
        $this->getJson('/api/v1/financial-goals/1/reservations')->assertOk()->assertJsonPath('data.accounts.0.reservedForGoal', 2000)->assertJsonPath('data.accounts.0.availableAmount', 1000);
        $this->getJson('/api/v1/financial-goals/1/eligible-expenses?account_id=1')->assertOk()->assertJsonCount(20, 'data.items')->assertJsonPath('data.hasMore', true);
        $this->getJson('/api/v1/financial-goals/1/eligible-expenses?account_id=1&page=2')->assertOk()->assertJsonCount(3, 'data.items')->assertJsonPath('data.hasMore', false);
    }

    public function test_recurring_occurrences_are_idempotent_and_have_no_account_transaction(): void
    {
        $entry = $this->add(['is_recurring' => true, 'recurring_transaction_id' => 10, 'amount' => 100]);
        $retry = $this->add(['is_recurring' => true, 'recurring_transaction_id' => 10, 'amount' => 100]);
        $this->assertSame($entry->id, $retry->id);
        $this->assertEquals(100, GoalReservations::reserved(1));
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_completed_goals_can_keep_and_use_reserves_without_reopening(): void
    {
        $this->add();
        app(FinancialGoalRepository::class)->complete($this->request(), '1');
        $this->assertEquals(2000, GoalReservations::reserved(1));
        $this->add(['type' => 'usage', 'category_id' => 1]);
        $this->assertEquals(0, GoalReservations::reserved(1));
        $this->assertEquals('completed', FinancialGoal::find(1)->status);
        $this->assertEquals(2000, FinancialGoal::find(1)->completed_funding);
    }

    public function test_reservations_across_goals_can_exceed_the_balance_and_preserve_cents(): void
    {
        $goal = FinancialGoal::create(['name' => 'Other goal', 'total_amount' => 1000, 'currency_id' => 1, 'funding_mode' => 'reservation']);
        DB::table('financial_goal_users')->insert(['financial_goal_id' => $goal->id, 'user_id' => 1, 'shared_role_id' => 1]);
        $this->add(['amount' => '2999.99']);
        $this->add(['financial_goal_id' => $goal->id, 'amount' => '0.01']);
        $this->assertEquals(0, GoalReservations::accountSummary(Account::find(1))['availableAmount']);
        $this->add(['financial_goal_id' => $goal->id, 'amount' => '0.01']);
        $this->assertEquals(3000.01, GoalReservations::reserved(1));
        $this->assertEquals(-0.01, GoalReservations::accountSummary(Account::find(1))['availableAmount']);
        $this->assertEquals(0.01, GoalReservations::accountSummary(Account::find(1))['reservationShortfall']);
        $this->assertEquals(3000, Account::find(1)->balance);
    }

    public function test_zero_and_negative_balances_allow_reservations_confirmation_and_spending(): void
    {
        Account::whereKey(1)->update(['balance' => 0]);
        $this->postJson('/api/v1/financial-goal-transactions', $this->request(['amount' => '10.01'])->all())->assertOk();
        $this->assertEquals(0, Account::find(1)->balance);
        Account::whereKey(1)->update(['balance' => '-100.25']);
        $this->postJson('/api/v1/financial-goal-transactions', $this->request(['amount' => '20.02'])->all())->assertOk();
        $pending = $this->add(['status' => 'pending', 'amount' => '30.03']);
        $this->postJson('/api/v1/financial-goal-transactions/'.$pending->id.'/confirm')->assertOk();
        $this->postJson('/api/v1/financial-goal-transactions/'.$pending->id.'/confirm')->assertOk();
        $this->assertEquals(60.06, GoalReservations::reserved(1, 1));
        $this->assertEquals(-100.25, Account::find(1)->balance);
        $this->postJson('/api/v1/financial-goal-transactions', $this->request(['type' => 'usage', 'category_id' => 1, 'amount' => '50.05'])->all())->assertOk();
        $this->assertEquals(-150.30, Account::find(1)->balance);
        $this->assertEquals(10.01, GoalReservations::reserved(1, 1));
        $this->postJson('/api/v1/financial-goal-transactions', $this->request(['type' => 'withdrawal', 'amount' => '10.01'])->all())->assertOk();
        $this->assertEquals(0, GoalReservations::reserved(1, 1));
        $this->assertEquals(-150.30, Account::find(1)->balance);
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_a_failure_after_creating_the_expense_rolls_back_the_entire_usage(): void
    {
        $this->add();
        $this->mock(ActivityLogRepository::class, fn ($mock) => $mock->shouldReceive('storeActivity')->andThrow(new \RuntimeException('Fixture failure')));
        try {
            $this->add(['type' => 'usage', 'category_id' => 1]);
            $this->fail();
        } catch (\RuntimeException $e) {
            $this->assertSame('Fixture failure', $e->getMessage());
        }
        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('financial_goal_transactions', 1);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertEquals(2000, GoalReservations::reserved(1));
    }

    public function test_retrying_an_unlinked_purchase_does_not_create_a_second_expense(): void
    {
        $this->add();
        $usage = $this->add(['type' => 'usage', 'category_id' => 1, 'request_key' => 'durable-purchase']);
        app(GoalReservations::class)->change($this->request(), (string) $usage->id, 'destroy');
        try {
            $this->add(['type' => 'usage', 'category_id' => 1, 'request_key' => 'durable-purchase']);
            $this->fail();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('request_key', $e->errors());
        }
        $this->assertDatabaseCount('transactions', 1);
        $this->assertEquals(1000, Account::find(1)->balance);
    }

    public function test_schema_rollback_refuses_to_destroy_reservation_history(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->migration()->down();
    }
    private function legacyEntry(array $entry = [], array $transaction = []): FinancialGoalTransaction
    {
        FinancialGoal::whereKey(1)->update(['funding_mode' => 'legacy', 'contributed_amount' => 100]);
        DB::table('categories')->insertOrIgnore([
            ['id' => 10, 'name' => 'Goal contribution', 'type' => 'expense', 'code' => 'financialGoalContribution', 'is_default' => true],
            ['id' => 11, 'name' => 'Goal withdrawal', 'type' => 'revenue', 'code' => 'financialGoalWithdrawal', 'is_default' => true],
        ]);
        $tx = Transaction::create(array_replace(['account_id' => 1, 'user_id' => 1, 'category_id' => 10,
            'amount' => 100, 'status' => 'completed', 'type' => 'expense', 'date' => today()->toDateString()], $transaction));

        return FinancialGoalTransaction::create(array_replace(['financial_goal_id' => 1, 'transaction_id' => $tx->id,
            'user_id' => 1, 'amount' => 100, 'type' => 'contribution', 'status' => 'completed', 'date' => today()->toDateString()], $entry));
    }

    public function test_rollout_uses_original_transaction_amount_restores_once_and_preserves_history_without_receipt_backfill(): void
    {
        $entry = $this->legacyEntry(['amount' => 80, 'status' => 'pending'], ['amount' => '125.25']);
        Account::whereKey(1)->update(['balance' => '2874.75']);
        FinancialGoal::whereKey(1)->update(['contributed_amount' => 999]);
        $original = DB::table('transactions')->where('id', $entry->transaction_id)->first();
        $rollout = app(MigrateGoalReservations::class);
        $preview = $rollout->run()[0];
        $this->assertSame('ready', $preview['status']);
        $this->assertEquals(125.25, $preview['fundedAmount']);
        $this->assertEquals(-873.75, $preview['difference']);
        $this->assertDatabaseCount('financial_goal_reservation_rollouts', 0);
        $this->assertEquals(2874.75, Account::find(1)->balance);
        $this->assertEquals(80, $entry->fresh()->amount);
        $this->assertSame('converted', $rollout->run(true)[0]['status']);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertEquals(125.25, $entry->fresh()->amount);
        $this->assertSame('completed', $entry->fresh()->status);
        $this->assertEquals($entry->transaction_id, $entry->fresh()->transaction_id);
        $this->assertEquals($original, DB::table('transactions')->where('id', $entry->transaction_id)->first());
        $this->assertEquals($entry->transaction_id, $entry->fresh()->transaction->id);
        $this->assertDatabaseCount('financial_goal_request_receipts', 0);
        $this->assertNull($entry->fresh()->request_key);
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 1);
        $this->assertEquals(125.25, GoalReservations::reserved(1, 1));
        $this->assertEquals(0, Transaction::count());
        DB::statement('CREATE VIEW transactions_view AS SELECT id, account_id AS accountId FROM transactions');
        $this->assertEquals(0, \Modules\Accounts\Entities\TransactionsView::count());
        $this->assertSame('already_reservation', $rollout->run(true)[0]['status']);
        $this->assertEquals(3000, Account::find(1)->balance);
        $snapshot = json_decode(DB::table('financial_goal_reservation_rollouts')->value('snapshot'), true);
        $this->assertEquals(80, $snapshot['entries'][0]['amount']);
        $this->assertSame('pending', $snapshot['entries'][0]['status']);
        $this->assertEquals(999, $snapshot['goal']['contributed_amount']);
    }

    public function test_rollout_preserves_pending_withdrawals_recurring_identity_and_completed_goals(): void
    {
        $this->legacyEntry();
        $withdrawal = $this->legacyEntry(['type' => 'withdrawal', 'amount' => 25], ['type' => 'revenue', 'category_id' => 11, 'amount' => 25]);
        $pending = $this->legacyEntry(['status' => 'pending', 'amount' => 50], ['status' => 'pending', 'amount' => 50, 'recurring_transaction_id' => 7]);
        FinancialGoal::whereKey(1)->update(['status' => 'completed', 'contributed_amount' => 75]);
        Account::whereKey(1)->update(['balance' => 2925]);
        $report = app(MigrateGoalReservations::class)->run(true)[0];
        $this->assertSame('converted', $report['status']);
        $this->assertEquals(75, GoalReservations::reserved(1, 1));
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertEquals('pending', $pending->fresh()->status);
        $this->assertEquals(7, $pending->fresh()->recurring_transaction_id);
        $this->assertEquals($withdrawal->transaction_id, $withdrawal->fresh()->transaction_id);
        $this->assertEquals('completed', FinancialGoal::find(1)->status);
        $this->assertEquals(75, FinancialGoal::find(1)->completed_funding);
        $this->assertDatabaseCount('transactions', 3);
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 3);
    }

    public function test_unreconciled_goal_does_not_abort_valid_goals_or_block_the_previous_contribution_flow(): void
    {
        $entry = $this->legacyEntry();
        DB::table('financial_goal_transactions')->where('id', $entry->id)->update(['transaction_id' => null]);
        $other = FinancialGoal::create(['name' => 'Another goal', 'total_amount' => 200, 'currency_id' => 1, 'funding_mode' => 'legacy']);
        $reports = app(MigrateGoalReservations::class)->run(true);
        $this->assertSame('needs_review', $reports[0]['status']);
        $this->assertSame('converted', $reports[1]['status']);
        $this->assertEquals('legacy', FinancialGoal::find(1)->funding_mode);
        $this->assertEquals('reservation', $other->fresh()->funding_mode);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 0);
        $this->postJson('/api/v1/financial-goal-transactions', $this->request(['amount' => 10])->all())->assertOk();
        $this->assertEquals(2990, Account::find(1)->balance);
        $this->assertEquals(110, FinancialGoal::find(1)->contributed_amount);
    }

    public function test_rollout_reports_negative_per_account_reserves_and_unknown_funded_history_without_changing_balances(): void
    {
        $this->legacyEntry(['type' => 'withdrawal', 'amount' => 25], ['type' => 'revenue', 'category_id' => 11, 'amount' => 25]);
        $empty = FinancialGoal::create(['name' => 'Unknown funding', 'total_amount' => 200, 'currency_id' => 1, 'funding_mode' => 'legacy', 'contributed_amount' => 50]);
        $reports = app(MigrateGoalReservations::class)->run(true);
        $this->assertSame('needs_review', $reports[0]['status']);
        $this->assertStringStartsWith('negative_historical_net_for_account', $reports[0]['issues'][0]['code']);
        $this->assertSame('funded_goal_without_history', $reports[1]['issues'][0]['code']);
        $this->assertEquals(50, $empty->fresh()->contributed_amount);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 0);
    }

    public function test_rollout_rejects_real_expenses_and_currency_mismatches_without_guessing_origins(): void
    {
        $entry = $this->legacyEntry([], ['category_id' => 1]);
        $report = app(MigrateGoalReservations::class)->run(true)[0];
        $this->assertSame('not_an_internal_goal_transaction', $report['issues'][0]['code']);
        DB::table('transactions')->where('id', $entry->transaction_id)->update(['category_id' => 10]);
        Account::whereKey(1)->update(['currency_id' => 2]);
        $report = app(MigrateGoalReservations::class)->run(true)[0];
        $this->assertSame('missing_account_or_currency_mismatch', $report['issues'][0]['code']);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 0);
    }

    public function test_old_partial_schema_and_existing_receipts_are_preserved_when_preparation_runs_again(): void
    {
        $entry = $this->legacyEntry(['request_key' => 'old-key', 'request_hash' => str_repeat('a', 64)]);
        DB::table('financial_goal_request_receipts')->insert(['request_key' => 'old-key', 'request_hash' => str_repeat('a', 64), 'entry_id' => $entry->id, 'financial_goal_id' => 1, 'user_id' => 1]);
        $this->migration()->up();
        $this->migration()->up();
        app(MigrateGoalReservations::class)->run(true);
        $this->assertDatabaseHas('financial_goal_request_receipts', ['request_key' => 'old-key', 'request_hash' => str_repeat('a', 64)]);
        $this->assertEquals('old-key', $entry->fresh()->request_key);
        $this->assertEquals(str_repeat('a', 64), $entry->fresh()->request_hash);
        $this->assertEquals(1, DB::table('financial_goal_request_receipts')->count());
    }

    public function test_normal_transaction_mutations_cannot_desynchronize_legacy_goal_totals_but_goal_editing_still_works(): void
    {
        $entry = $this->legacyEntry();
        foreach (['update', 'destroy', 'confirm'] as $action) {
            try {
                app(TransactionRepository::class)->$action($this->request(['amount' => 150]), (string) $entry->transaction_id);
                $this->fail('Linked internal movements must be managed through their goal.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('transaction_id', $e->errors());
            }
        }
        $this->assertEquals(100, $entry->fresh()->amount);
        app(\Modules\FinancialGoal\Repositories\FinancialGoalTransactionRepository::class)->update($this->request(['amount' => 150]), (string) $entry->id);
        $this->assertEquals(150, $entry->fresh()->amount);
        $this->assertEquals(150, FinancialGoal::find(1)->contributed_amount);
        $this->assertEquals(150, DB::table('transactions')->where('id', $entry->transaction_id)->value('amount'));
    }

    public function test_rollout_cli_is_read_only_by_default_and_reports_review_cases_with_an_explicit_exit_policy(): void
    {
        $entry = $this->legacyEntry();
        $this->artisan('goals:reservation-rollout', ['--goal' => '1'])->expectsOutputToContain('"mode": "preview"')->assertSuccessful();
        $this->assertDatabaseCount('financial_goal_reservation_rollouts', 0);
        $this->assertEquals('legacy', FinancialGoal::find(1)->funding_mode);
        DB::table('financial_goal_transactions')->where('id', $entry->id)->update(['transaction_id' => null]);
        $this->artisan('goals:reservation-rollout', ['--goal' => '1', '--fail-on-review' => true])->expectsOutputToContain('missing_transaction')->assertFailed();
        $this->artisan('goals:reservation-rollout', ['--goal' => '999'])->assertFailed();
        $this->assertDatabaseCount('financial_goal_reservation_rollouts', 0);
    }

    public function test_reservation_summary_uses_the_ledger_and_prior_successful_rollout_never_refunds_again(): void
    {
        $this->add(['amount' => 123.45]);
        FinancialGoal::whereKey(1)->update(['contributed_amount' => 999]);
        $this->assertEquals(123.45, GoalReservations::goalSummary(FinancialGoal::find(1))['reservedAmount']);
        $this->assertSame('already_reservation', app(MigrateGoalReservations::class)->run(true)[0]['status']);
        $this->assertEquals(123.45, FinancialGoal::find(1)->contributed_amount);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 0);
    }

    public function test_a_failed_balance_write_rolls_back_links_neutralization_and_totals_and_can_be_retried(): void
    {
        $entry = $this->legacyEntry(['amount' => 80], ['amount' => 125]);
        Account::whereKey(1)->update(['balance' => 2875]);
        DB::statement("CREATE TRIGGER fail_goal_balance BEFORE UPDATE ON accounts BEGIN SELECT RAISE(ABORT, 'fixture balance failure'); END");
        try {
            app(MigrateGoalReservations::class)->run(true);
            $this->fail('The balance failure must abort this goal conversion.');
        } catch (\Illuminate\Database\QueryException $error) {
            $this->assertStringContainsString('fixture balance failure', $error->getMessage());
        }
        $this->assertEquals(80, $entry->fresh()->amount);
        $this->assertEquals('legacy', FinancialGoal::find(1)->funding_mode);
        $this->assertEquals(2875, Account::find(1)->balance);
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 0);
        $this->assertDatabaseCount('financial_goal_reservation_rollouts', 0);
        DB::statement('DROP TRIGGER fail_goal_balance');
        $this->assertSame('converted', app(MigrateGoalReservations::class)->run(true)[0]['status']);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertEquals(125, GoalReservations::reserved(1, 1));
    }

    public function test_rollout_reserves_each_original_account_without_using_a_stale_direct_association(): void
    {
        Account::create(['id' => 2, 'name' => 'Wallet', 'balance' => -50, 'currency_id' => 1, 'active' => true]);
        $first = $this->legacyEntry(['account_id' => 2], ['account_id' => 1]);
        $second = $this->legacyEntry(['amount' => 50], ['amount' => 50, 'account_id' => 2]);
        $report = app(MigrateGoalReservations::class)->run(true)[0];
        $this->assertSame('converted', $report['status']);
        $this->assertEquals(1, $first->fresh()->account_id);
        $this->assertEquals(2, $second->fresh()->account_id);
        $this->assertEquals(100, GoalReservations::reserved(1, 1));
        $this->assertEquals(50, GoalReservations::reserved(2, 1));
        $this->assertEquals(150, FinancialGoal::find(1)->contributed_amount);
        $this->assertEquals(3100, Account::find(1)->balance);
        $this->assertEquals(0, Account::find(2)->balance);
    }

    public function test_legacy_due_movements_can_still_be_confirmed_and_deleted_from_the_goal_consistently(): void
    {
        $entry = $this->legacyEntry(['status' => 'pending'], ['status' => 'pending']);
        FinancialGoal::whereKey(1)->update(['contributed_amount' => 0]);
        $this->postJson('/api/v1/financial-goal-transactions/'.$entry->id.'/confirm')->assertOk();
        $this->assertEquals(100, FinancialGoal::find(1)->contributed_amount);
        $this->assertEquals(2900, Account::find(1)->balance);
        $this->deleteJson('/api/v1/financial-goal-transactions/'.$entry->id)->assertOk();
        $this->assertEquals(0, FinancialGoal::find(1)->contributed_amount);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_neutralized_internal_history_does_not_remove_accounts_from_net_worth_or_duplicate_shared_totals(): void
    {
        $this->legacyEntry();
        Account::create(['id' => 2, 'name' => 'Other bank', 'balance' => 3000, 'currency_id' => 1, 'active' => true]);
        DB::table('account_users')->insert(['account_id' => 2, 'user_id' => 1, 'shared_role_id' => 1]);
        Schema::table('currencies', fn (Blueprint $table) => $table->decimal('rate', 15, 6)->default(1));
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('currency_id');
        });
        DB::table('user_preferences')->insert(['user_id' => 1, 'currency_id' => 1]);
        DB::statement("CREATE VIEW accounts_view AS SELECT id, balance, currency_id AS currencyId, active AS status, '1' AS user_ids FROM accounts");
        $permission = DB::table('shared_permissions')->insertGetId(['code' => 'updateUserBalance']);
        DB::table('shared_permission_roles')->insert(['shared_role_id' => 1, 'shared_permission_id' => $permission]);
        auth()->user()->setRelation('preferences', (object) ['currency' => \Modules\Currency\Entities\Currency::find(1)]);
        app(MigrateGoalReservations::class)->run(true);
        DB::connection()->getPdo()->sqliteCreateFunction('FIND_IN_SET', fn ($id, $members) => in_array((string) $id, explode(',', $members), true) ? 1 : 0, 2);
        $stats = app(\Modules\Accounts\Repositories\AccountRepository::class)->getStats($this->request());
        $this->assertEquals(6100, $stats['netWorthAmount']);
        $this->assertEquals(0, $stats['totalExpensesAmount']);
        $this->assertEquals(0, $stats['totalRevenuesAmount']);
        $this->assertEquals(2, $stats['activeAccounts']);
    }

    public function test_legacy_detail_response_keeps_the_contribution_form_available_for_authorized_users(): void
    {
        $this->legacyEntry();
        $view = new \Modules\FinancialGoal\Entities\FinancialGoalView;
        $view->forceFill(['id' => 1, 'name' => 'Car', 'status' => 'in_progress', 'priority' => 'medium',
            'totalAmount' => 2000, 'contributedAmount' => 100, 'totalContributions' => 100, 'totalWithdrawals' => 0,
            'currencyCode' => 'EUR', 'currencySymbol' => '€', 'currencyId' => 1]);
        $view->setRelation('financialGoal', FinancialGoal::find(1));
        $view->setRelation('userContributions', []);
        $data = (new \Modules\FinancialGoal\Http\Resources\FinancialGoalViewResource($view))->resolve($this->request());
        $this->assertTrue($data['actions']['createTransactions']);
        $this->assertSame('legacy', $data['fundingMode']);
        $this->assertEquals(100, $data['fundedAmount']);
    }

}
