<?php

namespace Modules\FinancialGoal\Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\AccountsView;
use Modules\Accounts\Entities\Transaction;
use Modules\Accounts\Http\Resources\Accounts\AccountViewResource;
use Modules\Accounts\Repositories\AccountRepository;
use Modules\Accounts\Repositories\TransactionRepository;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Modules\Currency\Entities\Currency;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\FinancialGoal\Repositories\FinancialGoalRepository;
use Modules\FinancialGoal\Services\GoalReservations;
use Modules\FinancialGoal\Services\MigrateGoalReservations;
use Modules\User\Entities\User;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        $goal = FinancialGoal::create(['name' => 'First Car', 'total_amount' => 2000, 'currency_id' => 1]);
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

    public function test_new_reservation_events_store_currency_and_a_formatted_amount_snapshot(): void
    {
        $this->mock(ActivityLogRepository::class, function ($mock) {
            $mock->shouldReceive('storeActivity')->once()->withArgs(fn ($id, $userId, $type) => $type === 'account')->andReturnNull();
            $mock->shouldReceive('storeActivity')->times(3)->withArgs(function ($goalId, $userId, $type, $metadata) {
                if ($type !== 'financial_goal') {
                    return false;
                }
                $this->assertEquals(1, $metadata['currencyId']);
                $this->assertSame(Helpers::formatMoneyWithCurrency((float) $metadata['amount'], 'EUR', '€', true), $metadata['amountFallback']);

                return $goalId === 1;
            })->andReturnNull();
        });
        $this->add(['amount' => 100]);
        $this->add(['amount' => 20, 'status' => 'pending']);
        $this->add(['type' => 'usage', 'amount' => 30, 'category_id' => 1]);
    }

    public function test_deletion_preview_reports_reserves_without_changing_money(): void
    {
        $this->getJson('/api/v1/financial-goals/1/deletion-context')->assertOk()
            ->assertJsonPath('data.canDelete', true)->assertJsonPath('data.canReleaseAndCancel', false);
        $this->add(['amount' => 100]);
        $this->getJson('/api/v1/financial-goals/1/deletion-context')->assertOk()
            ->assertJsonPath('data.canDelete', false)->assertJsonPath('data.canReleaseAndCancel', true)
            ->assertJsonPath('data.reservedAmount', 100)->assertJsonPath('data.accounts.0.reservedAmount', 100);
        $this->deleteJson('/api/v1/financial-goals/1')->assertUnprocessable();
        $this->assertEquals(100, GoalReservations::reserved(1, 1));
        $this->assertEquals(3000, Account::find(1)->balance);
    }

    public function test_release_and_cancel_releases_each_origin_and_preserves_expenses_and_history_on_retry(): void
    {
        $other = Account::create(['name' => 'Second', 'balance' => 500, 'currency_id' => 1, 'active' => false]);
        DB::table('account_users')->insert(['account_id' => $other->id, 'user_id' => 1, 'shared_role_id' => 1]);
        $this->add(['amount' => 100]);
        $this->add(['type' => 'usage', 'amount' => 30, 'category_id' => 1]);
        FinancialGoalTransaction::create(['financial_goal_id' => 1, 'account_id' => $other->id, 'user_id' => 1,
            'amount' => 50, 'type' => 'contribution', 'status' => 'completed', 'date' => today()]);
        FinancialGoal::whereKey(1)->update(['status' => 'completed', 'completed_funding' => 150]);
        $this->postJson('/api/v1/financial-goals/1/release-and-cancel')->assertOk()->assertJsonPath('data.status', 'canceled');
        $this->postJson('/api/v1/financial-goals/1/release-and-cancel')->assertOk();
        $this->assertEquals(0, GoalReservations::reserved(1, 1));
        $this->assertEquals(0, GoalReservations::reserved($other->id, 1));
        $this->assertEquals(2970, Account::find(1)->balance);
        $this->assertEquals(500, $other->fresh()->balance);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('financial_goal_transactions', 5);
        $this->assertEquals(150, FinancialGoal::find(1)->completed_funding);
        $this->assertEquals(30, GoalReservations::goalSummary(FinancialGoal::find(1))['usedAmount']);
        $this->getJson('/api/v1/financial-goals/1/deletion-context')->assertOk()
            ->assertJsonPath('data.canDelete', false)->assertJsonPath('data.canReleaseAndCancel', false);
    }

    public function test_pending_movements_block_automatic_release_without_partial_changes(): void
    {
        $this->add(['amount' => 100]);
        $this->add(['amount' => 20, 'status' => 'pending']);
        $this->getJson('/api/v1/financial-goals/1/deletion-context')->assertOk()
            ->assertJsonPath('data.pendingCount', 1)->assertJsonPath('data.canReleaseAndCancel', false);
        $this->postJson('/api/v1/financial-goals/1/release-and-cancel')->assertUnprocessable()->assertJsonValidationErrors('financial_goal_id');
        $this->assertEquals(100, GoalReservations::reserved(1, 1));
        $this->assertDatabaseCount('financial_goal_transactions', 2);
        $this->assertEquals('in_progress', FinancialGoal::find(1)->status);
    }

    public function test_inaccessible_origin_blocks_and_rolls_back_releases_from_other_accounts(): void
    {
        $this->add(['amount' => 100]);
        $other = Account::create(['name' => 'Secret bank', 'balance' => 500, 'currency_id' => 1]);
        FinancialGoalTransaction::create(['financial_goal_id' => 1, 'account_id' => $other->id, 'user_id' => 2,
            'amount' => 50, 'type' => 'contribution', 'status' => 'completed', 'date' => today()]);
        $this->getJson('/api/v1/financial-goals/1/deletion-context')->assertOk()
            ->assertJsonPath('data.canReleaseAndCancel', false)->assertJsonPath('data.accounts.1.id', null)
            ->assertJsonPath('data.accounts.1.name', null);
        $this->postJson('/api/v1/financial-goals/1/release-and-cancel')->assertForbidden();
        $this->assertEquals(100, GoalReservations::reserved(1, 1));
        $this->assertEquals(50, GoalReservations::reserved($other->id, 1));
        $this->assertDatabaseCount('financial_goal_transactions', 2);
        $this->assertEquals('in_progress', FinancialGoal::find(1)->status);
        $this->actingAs((new User)->forceFill(['id' => 2, 'name' => 'Other']));
        $this->getJson('/api/v1/financial-goals/1/deletion-context')->assertForbidden();
        $this->postJson('/api/v1/financial-goals/1/release-and-cancel')->assertForbidden();
    }

    public function test_account_goal_breakdown_tracks_contributions_withdrawals_and_spending(): void
    {
        FinancialGoal::whereKey(1)->update(['name' => 'Férias']);
        $this->add(['amount' => 100]);
        $this->assertEquals(2900, GoalReservations::accountSummary(Account::find(1))['availableAmount']);
        $this->assertSame([['id' => '1', 'name' => 'Férias', 'status' => 'in_progress',
            'fundedAmount' => 100.0, 'usedAmount' => 0.0, 'reservedAmount' => 100]], GoalReservations::accountGoals(Account::find(1), 1));
        $this->add(['type' => 'withdrawal', 'amount' => 20]);
        $this->add(['type' => 'usage', 'amount' => 30, 'category_id' => 1]);
        $this->add(['amount' => 99, 'status' => 'pending']);
        $row = GoalReservations::accountGoals(Account::find(1), 1)[0];
        $this->assertEquals(80, $row['fundedAmount']);
        $this->assertEquals(30, $row['usedAmount']);
        $this->assertEquals(50, $row['reservedAmount']);
        $this->assertEquals(2970, Account::find(1)->balance);
        $this->assertEquals(2920, GoalReservations::accountSummary(Account::find(1))['availableAmount']);

        $view = (new AccountsView)->forceFill([
            'id' => 1, 'name' => 'Teste', 'type' => 'bank_account', 'balance' => 2970,
            'currencyCode' => 'EUR', 'currencySymbol' => '€', 'currencyId' => 1,
            'status' => true, 'totalRevenues' => 0, 'totalExpenses' => 30,
        ])->setRelation('account', Account::find(1))
            ->setRelation('users', collect())->setRelation('invites', collect());
        $resource = (new AccountViewResource($view))->resolve($this->request());
        $this->assertSame([$row], $resource['goalReservations']);
        $this->assertEquals(2920, $resource['availableAmount']);
    }

    public function test_account_goal_breakdown_scopes_accounts_permissions_and_duplicate_memberships(): void
    {
        $this->add(['amount' => 100]);
        DB::table('financial_goal_users')->insert(['financial_goal_id' => 1, 'user_id' => 1, 'shared_role_id' => 1]);
        $private = FinancialGoal::create(['name' => 'Private', 'total_amount' => 100, 'currency_id' => 1]);
        FinancialGoalTransaction::create(['financial_goal_id' => $private->id, 'account_id' => 1,
            'user_id' => 2, 'type' => 'contribution', 'amount' => 50, 'status' => 'completed', 'date' => today()]);
        DB::table('shared_roles')->insert(['id' => 2, 'code' => 'restricted', 'name' => '{"en":"Restricted"}']);
        DB::table('financial_goal_users')->insert(['financial_goal_id' => $private->id, 'user_id' => 1, 'shared_role_id' => 2]);
        $other = Account::create(['name' => 'Other bank', 'balance' => 1000, 'currency_id' => 1]);
        DB::table('account_users')->insert(['account_id' => $other->id, 'user_id' => 1, 'shared_role_id' => 1]);
        $this->add(['account_id' => $other->id, 'amount' => 75]);
        $rows = GoalReservations::accountGoals(Account::find(1), 1);
        $this->assertCount(1, $rows);
        $this->assertEquals(100, $rows[0]['reservedAmount']);
        $this->assertEquals(150, GoalReservations::accountSummary(Account::find(1))['reservedAmount']);
        $this->assertEquals(75, GoalReservations::accountGoals($other, 1)[0]['reservedAmount']);
        DB::table('account_users')->insert(['account_id' => 1, 'user_id' => 2, 'shared_role_id' => 1]);
        $this->assertSame([], GoalReservations::accountGoals(Account::find(1), 2));
        $this->expectException(HttpException::class);
        GoalReservations::accountGoals($other, 2);
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
        $goal = FinancialGoal::create(['name' => 'Other goal', 'total_amount' => 1000, 'currency_id' => 1]);
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

    private function historicalEntry(array $entry = [], array $transaction = []): FinancialGoalTransaction
    {
        FinancialGoal::whereKey(1)->update(['contributed_amount' => 100]);
        DB::table('categories')->insertOrIgnore([
            ['id' => 10, 'name' => 'Goal contribution', 'type' => 'expense', 'code' => 'financialGoalContribution', 'is_default' => true],
            ['id' => 11, 'name' => 'Goal withdrawal', 'type' => 'revenue', 'code' => 'financialGoalWithdrawal', 'is_default' => true],
        ]);
        $tx = Transaction::create(array_replace(['account_id' => 1, 'user_id' => 1, 'category_id' => 10,
            'amount' => 100, 'status' => 'completed', 'type' => 'expense', 'date' => today()->toDateString()], $transaction));

        return FinancialGoalTransaction::create(array_replace(['financial_goal_id' => 1, 'transaction_id' => $tx->id,
            'user_id' => 1, 'amount' => 100, 'type' => 'contribution', 'status' => 'completed', 'date' => today()->toDateString()], $entry));
    }

    public function test_mixed_goal_history_from_the_goal_7_regression_is_fully_assigned_and_totals_121_40(): void
    {
        // Sanitized amounts only; no descriptions, user identifiers or original account data.
        $amounts = ['12.50', '4.00', '1.00', '12.50', '4.25', '2.50', '3.00', '5.00', '10.00', '8.15',
            '20.00', '10.00', '2.00', '4.30', '1.50', '2.00', '1.25', '1.25', '4.20', '12.00'];
        foreach ($amounts as $amount) {
            $this->historicalEntry(['amount' => $amount], ['amount' => $amount]);
        }
        $this->historicalEntry(['type' => 'withdrawal', 'amount' => 10], ['type' => 'revenue', 'category_id' => 11, 'amount' => 10]);
        FinancialGoalTransaction::create(['financial_goal_id' => 1, 'user_id' => 1, 'account_id' => 1,
            'type' => 'contribution', 'amount' => 10, 'status' => 'completed', 'date' => today()->toDateString()]);
        FinancialGoal::whereKey(1)->update(['contributed_amount' => 10]);
        Account::whereKey(1)->update(['balance' => 1000]);
        // Reproduce the previous flag incorrectly marking this entire mixed goal as converted.
        Schema::table('financial_goals', fn (Blueprint $table) => $table->string('funding_mode')->default('reservation'));
        $preview = app(MigrateGoalReservations::class)->run()[0];
        $this->assertEquals(121.4, $preview['reservedAmount']);
        $this->assertCount(21, $preview['assignedMovements']);
        $this->assertEquals(111.4, $preview['accounts'][0]['balanceAdjustment']);
        (require base_path('Modules/FinancialGoal/Database/Migrations/2026_11_02_230000_unify_all_goal_reservations.php'))->up();
        $this->assertFalse(Schema::hasColumn('financial_goals', 'funding_mode'));
        $this->assertEquals(121.4, GoalReservations::reserved(1, 1));
        $this->assertEquals(121.4, FinancialGoal::find(1)->contributed_amount);
        $this->assertEquals(1111.4, Account::find(1)->balance);
        $this->assertEquals(0, FinancialGoalTransaction::whereNull('account_id')->count());
        $this->assertDatabaseCount('transactions', 21);
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 21);
        $this->getJson('/api/v1/financial-goals/1/reservations')->assertOk()
            ->assertJsonPath('data.reservedAmount', 121.4)->assertJsonPath('data.accounts.0.reservedForGoal', 121.4)
            ->assertJsonMissingPath('data.fundingMode');
        $events = DB::table('financial_goal_reservation_rollout_events')->count();
        $this->assertSame('unchanged', app(MigrateGoalReservations::class)->run(true)[0]['status']);
        $this->assertEquals(1111.4, Account::find(1)->balance);
        $this->assertEquals($events, DB::table('financial_goal_reservation_rollout_events')->count());
    }

    public function test_goal_ledger_amounts_are_preserved_while_account_adjustments_use_the_actual_original_debits(): void
    {
        $entry = $this->historicalEntry(['amount' => 80], ['amount' => 125.25, 'category_id' => 1]);
        FinancialGoal::whereKey(1)->update(['contributed_amount' => 999]);
        Account::whereKey(1)->update(['balance' => 2874.75]);
        $original = DB::table('transactions')->where('id', $entry->transaction_id)->first();
        $preview = app(MigrateGoalReservations::class)->run()[0];
        $this->assertEquals(80, $preview['reservedAmount']);
        $this->assertEquals(125.25, $preview['accounts'][0]['balanceAdjustment']);
        $this->assertCount(1, $preview['transactionDifferences']);
        $this->assertDatabaseCount('financial_goal_reservation_rollouts', 0);
        $this->assertEquals(2874.75, Account::find(1)->balance);
        app(MigrateGoalReservations::class)->run(true);
        $this->assertEquals(80, $entry->fresh()->amount);
        $this->assertEquals(80, FinancialGoal::find(1)->contributed_amount);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertEquals($original, DB::table('transactions')->where('id', $entry->transaction_id)->first());
        $this->assertEquals($entry->transaction_id, $entry->fresh()->transaction->id);
        $this->assertDatabaseCount('financial_goal_request_receipts', 0);
        $this->assertEquals(0, Transaction::count());
        $snapshot = json_decode(DB::table('financial_goal_reservation_rollouts')->value('snapshot'), true);
        $this->assertEquals(999, $snapshot['goal']['contributed_amount']);
    }

    public function test_pending_movement_state_is_preserved_even_if_the_underlying_account_transaction_was_confirmed_separately(): void
    {
        $entry = $this->historicalEntry(['status' => 'pending'], ['status' => 'completed']);
        Account::whereKey(1)->update(['balance' => 2900]);
        app(MigrateGoalReservations::class)->run(true);
        $this->assertEquals(0, GoalReservations::reserved(1, 1));
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertSame('pending', $entry->fresh()->status);
        $this->postJson('/api/v1/financial-goal-transactions/'.$entry->id.'/confirm')->assertOk();
        $this->assertEquals(100, GoalReservations::reserved(1, 1));
        $this->assertEquals(3000, Account::find(1)->balance);
    }

    public function test_missing_origins_abort_the_entire_migration_instead_of_leaving_two_models(): void
    {
        $valid = $this->historicalEntry();
        $other = FinancialGoal::create(['name' => 'Other goal', 'total_amount' => 200, 'currency_id' => 1, 'contributed_amount' => 40]);
        $bad = FinancialGoalTransaction::create(['financial_goal_id' => $other->id, 'user_id' => 1,
            'amount' => 40, 'type' => 'contribution', 'status' => 'completed', 'date' => today()->toDateString()]);
        $reports = app(MigrateGoalReservations::class)->run();
        $this->assertSame('ready', $reports[0]['status']);
        $this->assertSame('blocked', $reports[1]['status']);
        try {
            app(MigrateGoalReservations::class)->run(true);
            $this->fail('Missing origin must abort the complete conversion.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('No goals were converted', $e->getMessage());
        }
        $this->assertNull($valid->fresh()->account_id);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 0);
        $this->assertDatabaseCount('financial_goal_reservation_rollouts', 0);
        DB::table('financial_goal_transactions')->where('id', $bad->id)->update(['account_id' => 1]);
        app(MigrateGoalReservations::class)->run(true);
        $this->assertEquals(100, GoalReservations::reserved(1, 1));
        $this->assertEquals(40, GoalReservations::reserved(1, $other->id));
        $this->assertEquals(3100, Account::find(1)->balance);
    }

    public function test_direct_reservations_and_real_usage_are_kept_without_refunding_the_purchase(): void
    {
        $this->add(['amount' => 20]);
        $usage = $this->add(['type' => 'usage', 'amount' => 1, 'category_id' => 1]);
        $balance = Account::find(1)->balance;
        app(MigrateGoalReservations::class)->run(true);
        $this->assertEquals(19, GoalReservations::reserved(1, 1));
        $this->assertEquals(20, FinancialGoal::find(1)->contributed_amount);
        $this->assertEquals($balance, Account::find(1)->balance);
        $this->assertEquals(1, Transaction::count());
        $this->assertNotNull(Transaction::find($usage->transaction_id));
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 0);
    }

    public function test_unknown_funded_history_negative_account_reserves_and_wrong_transaction_types_block_conversion(): void
    {
        FinancialGoal::whereKey(1)->update(['contributed_amount' => 50]);
        $report = app(MigrateGoalReservations::class)->run()[0];
        $this->assertSame('funded_goal_without_history', $report['issues'][0]['code']);
        $entry = $this->historicalEntry(['type' => 'withdrawal', 'amount' => 25], ['type' => 'revenue', 'category_id' => 11, 'amount' => 25]);
        $report = app(MigrateGoalReservations::class)->run()[0];
        $this->assertStringStartsWith('negative_historical_net_for_account', $report['issues'][0]['code']);
        DB::table('transactions')->where('id', $entry->transaction_id)->update(['type' => 'expense']);
        $report = app(MigrateGoalReservations::class)->run()[0];
        $this->assertSame('not_an_internal_goal_transaction', $report['issues'][0]['code']);
        $this->assertEquals(3000, Account::find(1)->balance);
    }

    public function test_preparation_and_repeated_conversion_keep_existing_receipts_and_the_original_snapshot(): void
    {
        $entry = $this->historicalEntry(['request_key' => 'old-key', 'request_hash' => str_repeat('a', 64)]);
        DB::table('financial_goal_request_receipts')->insert(['request_key' => 'old-key', 'request_hash' => str_repeat('a', 64), 'entry_id' => $entry->id, 'financial_goal_id' => 1, 'user_id' => 1]);
        $this->migration()->up();
        $this->migration()->up();
        app(MigrateGoalReservations::class)->run(true);
        $snapshot = DB::table('financial_goal_reservation_rollouts')->value('snapshot');
        $this->assertSame('unchanged', app(MigrateGoalReservations::class)->run(true)[0]['status']);
        $this->assertEquals($snapshot, DB::table('financial_goal_reservation_rollouts')->value('snapshot'));
        $this->assertDatabaseHas('financial_goal_request_receipts', ['request_key' => 'old-key', 'request_hash' => str_repeat('a', 64)]);
        $this->assertEquals('old-key', $entry->fresh()->request_key);
        $this->assertDatabaseCount('financial_goal_reservation_rollout_events', 1);
    }

    public function test_rollout_cli_is_read_only_and_reports_an_unresolved_origin_before_any_writes(): void
    {
        $entry = $this->historicalEntry();
        $this->artisan('goals:reservation-rollout', ['--goal' => '1'])->expectsOutputToContain('"mode": "preview"')->assertSuccessful();
        $this->assertDatabaseCount('financial_goal_reservation_rollouts', 0);
        DB::table('financial_goal_transactions')->where('id', $entry->id)->update(['transaction_id' => null]);
        $this->artisan('goals:reservation-rollout', ['--goal' => '1', '--fail-on-review' => true])->expectsOutputToContain('blocked')->assertFailed();
        $this->artisan('goals:reservation-rollout', ['--apply' => true])->expectsOutputToContain('No goals were converted')->assertFailed();
        $this->assertDatabaseCount('financial_goal_reservation_rollouts', 0);
    }

    public function test_a_failure_after_an_earlier_goal_was_written_rolls_back_every_goal_and_can_be_retried(): void
    {
        $first = $this->historicalEntry();
        Account::create(['id' => 2, 'name' => 'Wallet', 'balance' => 1000, 'currency_id' => 1, 'active' => true]);
        $other = FinancialGoal::create(['name' => 'Other', 'total_amount' => 200, 'currency_id' => 1]);
        $second = $this->historicalEntry(['financial_goal_id' => $other->id], ['account_id' => 2]);
        DB::statement("CREATE TRIGGER fail_goal_balance BEFORE UPDATE ON accounts WHEN NEW.id = 2 BEGIN SELECT RAISE(ABORT, 'fixture balance failure'); END");
        try {
            app(MigrateGoalReservations::class)->run(true);
            $this->fail('The write failure must abort every goal.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('fixture balance failure', $e->getMessage());
        }
        $this->assertNull($first->fresh()->account_id);
        $this->assertNull($second->fresh()->account_id);
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 0);
        $this->assertDatabaseCount('financial_goal_reservation_rollout_events', 0);
        DB::statement('DROP TRIGGER fail_goal_balance');
        app(MigrateGoalReservations::class)->run(true);
        $this->assertEquals(3100, Account::find(1)->balance);
        $this->assertEquals(1100, Account::find(2)->balance);
    }

    public function test_imported_markers_skip_only_their_transaction_and_never_skip_other_entries_of_the_goal(): void
    {
        $done = $this->historicalEntry(['account_id' => 1]);
        DB::table('financial_goal_neutralized_transactions')->insert(['transaction_id' => $done->transaction_id,
            'financial_goal_id' => 1, 'balance_adjustment' => 100, 'created_at' => now()]);
        $missing = $this->historicalEntry(['amount' => 20], ['amount' => 20]);
        app(MigrateGoalReservations::class)->run(true);
        $this->assertEquals(3020, Account::find(1)->balance);
        $this->assertEquals(120, GoalReservations::reserved(1, 1));
        $this->assertEquals(1, $missing->fresh()->account_id);
        $this->assertDatabaseCount('financial_goal_neutralized_transactions', 2);
    }

    public function test_a_previous_converter_audit_recovers_the_account_without_refunding_twice(): void
    {
        $entry = $this->historicalEntry();
        $tx = DB::table('transactions')->where('id', $entry->transaction_id)->first();
        DB::table('financial_goal_transactions')->where('id', $entry->id)->update(['transaction_id' => null]);
        DB::table('transactions')->where('id', $tx->id)->delete();
        Schema::create('financial_goal_conversions', function (Blueprint $table) {
            $table->unsignedBigInteger('financial_goal_id');
            $table->json('snapshot');
        });
        DB::table('financial_goal_conversions')->insert(['financial_goal_id' => 1, 'snapshot' => json_encode([
            'entries' => [$entry->getAttributes()], 'transactions' => [$tx],
            'conversion' => ['balancePolicy' => 'restore_internal_movements'],
        ])]);
        app(MigrateGoalReservations::class)->run(true);
        $this->assertEquals(100, GoalReservations::reserved(1, 1));
        $this->assertEquals(3000, Account::find(1)->balance);
        $this->assertEquals(1, $entry->fresh()->account_id);
    }

    public function test_neutralized_history_does_not_remove_account_balances_from_the_dashboard(): void
    {
        $this->historicalEntry();
        Schema::table('currencies', fn (Blueprint $t) => $t->decimal('rate', 15, 6)->default(1));
        Schema::create('user_preferences', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('currency_id');
        });
        DB::table('user_preferences')->insert(['user_id' => 1, 'currency_id' => 1]);
        DB::statement("CREATE VIEW accounts_view AS SELECT id, balance, currency_id AS currencyId, active AS status, '1' AS user_ids FROM accounts");
        $permission = DB::table('shared_permissions')->insertGetId(['code' => 'updateUserBalance']);
        DB::table('shared_permission_roles')->insert(['shared_role_id' => 1, 'shared_permission_id' => $permission]);
        auth()->user()->setRelation('preferences', (object) ['currency' => Currency::find(1)]);
        app(MigrateGoalReservations::class)->run(true);
        DB::connection()->getPdo()->sqliteCreateFunction('FIND_IN_SET', fn ($id, $members) => in_array((string) $id, explode(',', $members), true) ? 1 : 0, 2);
        $stats = app(AccountRepository::class)->getStats($this->request());
        $this->assertEquals(3100, $stats['netWorthAmount']);
        $this->assertEquals(0, $stats['totalExpensesAmount']);
    }
}
