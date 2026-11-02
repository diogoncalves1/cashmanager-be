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
use Modules\FinancialGoal\Services\ConvertGoalReservations;
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
        return require base_path('Modules/FinancialGoal/Database/Migrations/2026_11_01_180000_add_goal_reservations.php');
    }

    private function accountBackfillMigration()
    {
        return require base_path('Modules/FinancialGoal/Database/Migrations/2026_11_02_180000_backfill_financial_goal_accounts.php');
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

    public function test_conversion_is_explicit_reconciled_audited_and_rejects_stale_previews(): void
    {
        FinancialGoal::whereKey(1)->update(['funding_mode' => 'legacy', 'contributed_amount' => 2000]);
        Account::whereKey(1)->update(['balance' => 1000]);
        DB::table('categories')->where('id', 1)->update(['code' => 'financialGoalContribution', 'is_default' => true, 'user_id' => null]);
        $expense = Transaction::create(['account_id' => 1, 'user_id' => 1, 'category_id' => 1, 'type' => 'expense', 'amount' => 2000, 'date' => today()->toDateString(), 'status' => 'completed']);
        FinancialGoalTransaction::create(['financial_goal_id' => 1, 'user_id' => 1, 'transaction_id' => $expense->id, 'type' => 'contribution', 'amount' => 2000, 'date' => today()->toDateString(), 'status' => 'completed']);
        $preview = app(ConvertGoalReservations::class)->preview($this->request(), '1');
        $this->assertEquals(3000, $preview['accounts'][0]['balanceAfter']);
        $this->assertEquals('legacy', FinancialGoal::find(1)->funding_mode);
        Account::whereKey(1)->update(['balance' => 1100]);
        $this->postJson('/api/v1/financial-goals/1/reservation-conversion', ['confirm' => true, 'token' => $preview['token']])->assertUnprocessable()->assertJsonValidationErrors('token');
        $preview = app(ConvertGoalReservations::class)->preview($this->request(), '1');
        $this->postJson('/api/v1/financial-goals/1/reservation-conversion', ['confirm' => true, 'token' => $preview['token']])->assertOk();
        $this->assertEquals(3100, Account::find(1)->balance);
        $this->assertEquals(2000, GoalReservations::reserved(1));
        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('financial_goal_transactions', 1);
        $this->assertDatabaseCount('financial_goal_conversions', 1);
        $snapshot = json_decode(DB::table('financial_goal_conversions')->value('snapshot'), true);
        $this->assertEquals(2000, $snapshot['transactions'][0]['amount']);
        $this->postJson('/api/v1/financial-goals/1/reservation-conversion', ['confirm' => true, 'token' => $preview['token']])->assertUnprocessable();
        $this->assertEquals(3100, Account::find(1)->balance);
    }

    public function test_migrations_recover_existing_accounts_without_changing_balances_or_double_counting(): void
    {
        FinancialGoal::whereKey(1)->update(['funding_mode' => 'legacy', 'contributed_amount' => 2000]);
        $this->migration()->down();
        Account::whereKey(1)->update(['balance' => 1000]);
        Account::create(['id' => 2, 'name' => 'Savings', 'balance' => 250, 'currency_id' => 1, 'active' => true]);
        DB::table('account_users')->insert(['account_id' => 2, 'user_id' => 1, 'shared_role_id' => 1]);
        DB::table('categories')->where('id', 1)->update(['code' => 'financialGoalContribution', 'is_default' => true, 'user_id' => null]);
        DB::table('categories')->insert(['id' => 2, 'name' => 'Release', 'type' => 'revenue', 'code' => 'financialGoalWithdrawal', 'is_default' => true]);
        $origins = [];
        foreach ([[1, 'contribution', 1200, 'completed'], [2, 'contribution', 1000, 'completed'], [1, 'withdrawal', 200, 'completed'], [2, 'contribution', 50, 'pending']] as [$accountId, $type, $amount, $status]) {
            $transaction = Transaction::create(['account_id' => $accountId, 'user_id' => 1, 'category_id' => $type === 'contribution' ? 1 : 2,
                'type' => $type === 'contribution' ? 'expense' : 'revenue', 'amount' => $amount, 'status' => $status, 'date' => today()->toDateString()]);
            $entry = FinancialGoalTransaction::create(['financial_goal_id' => 1, 'user_id' => 1, 'transaction_id' => $transaction->id,
                'type' => $type, 'amount' => $amount, 'status' => $status, 'date' => today()->toDateString()]);
            $origins[$entry->id] = $accountId;
        }
        $oldTransactions = DB::table('transactions')->orderBy('id')->get()->toArray();
        $oldEntries = DB::table('financial_goal_transactions')->orderBy('id')->get()->toArray();

        // The schema migration and data migration are both applied to existing rows.
        $this->migration()->up();
        $this->accountBackfillMigration()->up();
        $this->accountBackfillMigration()->up();
        foreach ($oldEntries as $original) {
            $entry = FinancialGoalTransaction::findOrFail($original->id);
            $this->assertEquals($origins[$original->id], $entry->account_id);
            foreach (get_object_vars($original) as $field => $value) {
                $this->assertEquals($value, $entry->getRawOriginal($field));
            }
        }
        $this->assertEquals($oldTransactions, DB::table('transactions')->orderBy('id')->get()->toArray());
        $this->assertEquals('legacy', FinancialGoal::find(1)->funding_mode);
        $this->assertEquals(2000, FinancialGoal::find(1)->contributed_amount);
        $this->assertEquals(1000, Account::find(1)->balance);
        $this->assertEquals(250, Account::find(2)->balance);
        $this->assertEquals(0, GoalReservations::reserved(1));
        $this->assertEquals(0, GoalReservations::reserved(2));
        $this->assertEquals(1000, GoalReservations::accountSummary(Account::find(1))['availableAmount']);
        $this->assertDatabaseCount('financial_goal_conversions', 0);

        // Existing and new goals can coexist until the explicit conversion.
        $newGoal = FinancialGoal::create(['name' => 'New goal', 'total_amount' => 1000, 'currency_id' => 1, 'funding_mode' => 'reservation']);
        DB::table('financial_goal_users')->insert(['financial_goal_id' => $newGoal->id, 'user_id' => 1, 'shared_role_id' => 1]);
        $this->add(['financial_goal_id' => $newGoal->id, 'amount' => '125.25']);
        $this->accountBackfillMigration()->up();
        $this->assertEquals(125.25, GoalReservations::reserved(1));
        $preview = app(ConvertGoalReservations::class)->preview($this->request(), '1');
        $this->postJson('/api/v1/financial-goals/1/reservation-conversion', ['confirm' => true, 'token' => $preview['token']])->assertOk();
        $this->assertEquals(1125.25, GoalReservations::reserved(1));
        $this->assertEquals(1000, GoalReservations::reserved(2));
        $this->assertEquals(874.75, GoalReservations::accountSummary(Account::find(1))['availableAmount']);
        $this->assertEquals(250, GoalReservations::accountSummary(Account::find(2))['availableAmount']);
        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('financial_goal_transactions', 5);
    }

    public function test_account_backfill_preserves_unknown_origins_and_existing_associations_for_review(): void
    {
        FinancialGoal::whereKey(1)->update(['funding_mode' => 'legacy', 'contributed_amount' => 100]);
        $unresolved = FinancialGoalTransaction::create(['financial_goal_id' => 1, 'user_id' => 1, 'transaction_id' => null,
            'type' => 'contribution', 'amount' => 100, 'date' => today()->toDateString(), 'status' => 'completed']);
        $transaction = Transaction::create(['account_id' => 1, 'user_id' => 1, 'category_id' => 1, 'type' => 'expense', 'amount' => 100, 'date' => today()->toDateString(), 'status' => 'completed']);
        Account::create(['id' => 2, 'name' => 'Reviewed account', 'balance' => 0, 'currency_id' => 1, 'active' => true]);
        $assigned = FinancialGoalTransaction::create(['financial_goal_id' => 1, 'user_id' => 1, 'transaction_id' => $transaction->id, 'account_id' => 2,
            'type' => 'contribution', 'amount' => 100, 'date' => today()->toDateString(), 'status' => 'pending']);
        $missingAccountTransaction = Transaction::create(['account_id' => 999, 'user_id' => 1, 'category_id' => 1, 'type' => 'expense', 'amount' => 50, 'date' => today()->toDateString(), 'status' => 'completed']);
        $missingAccountEntry = FinancialGoalTransaction::create(['financial_goal_id' => 1, 'user_id' => 1, 'transaction_id' => $missingAccountTransaction->id,
            'type' => 'contribution', 'amount' => 50, 'date' => today()->toDateString(), 'status' => 'completed']);
        $this->accountBackfillMigration()->up();
        $this->accountBackfillMigration()->down();
        $this->assertNull($unresolved->fresh()->account_id);
        $this->assertNull($missingAccountEntry->fresh()->account_id);
        $this->assertEquals(2, $assigned->fresh()->account_id);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->getJson('/api/v1/financial-goals/1/reservation-conversion')->assertUnprocessable()->assertJsonValidationErrors('financial_goal_id');
        $this->assertDatabaseCount('financial_goal_conversions', 0);
    }

    public function test_conversion_rejects_a_backfilled_account_that_disagrees_with_its_transaction(): void
    {
        FinancialGoal::whereKey(1)->update(['funding_mode' => 'legacy', 'contributed_amount' => 100]);
        DB::table('categories')->where('id', 1)->update(['code' => 'financialGoalContribution', 'is_default' => true, 'user_id' => null]);
        Account::create(['id' => 2, 'name' => 'Other account', 'balance' => 0, 'currency_id' => 1, 'active' => true]);
        $transaction = Transaction::create(['account_id' => 1, 'user_id' => 1, 'category_id' => 1, 'type' => 'expense', 'amount' => 100, 'date' => today()->toDateString(), 'status' => 'completed']);
        FinancialGoalTransaction::create(['financial_goal_id' => 1, 'user_id' => 1, 'transaction_id' => $transaction->id, 'account_id' => 2,
            'type' => 'contribution', 'amount' => 100, 'date' => today()->toDateString(), 'status' => 'completed']);
        $this->getJson('/api/v1/financial-goals/1/reservation-conversion')->assertUnprocessable()->assertJsonValidationErrors('account_id');
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertDatabaseCount('financial_goal_conversions', 0);
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
}
