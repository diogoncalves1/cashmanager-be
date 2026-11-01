<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Accounts\Repositories\AccountResumeRepository;
use Modules\Currency\Entities\Currency;
use Modules\User\Entities\User;
use Tests\TestCase;

class AccountResumeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->travelTo(Carbon::parse('2026-10-31 12:00:00'));
        app()->setLocale('pt');
        Schema::create('currencies', function (Blueprint $t) {
            $t->id();
            $t->string('code');
        });
        Schema::create('accounts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type');
            $t->boolean('active');
            $t->decimal('balance', 15, 2);
            $t->unsignedBigInteger('currency_id');
        });
        Schema::create('account_users', function (Blueprint $t) {
            $t->unsignedBigInteger('account_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('shared_role_id');
        });
        Schema::create('shared_permissions', function (Blueprint $t) {
            $t->id();
            $t->string('code');
        });
        Schema::create('shared_permission_roles', function (Blueprint $t) {
            $t->unsignedBigInteger('shared_role_id');
            $t->unsignedBigInteger('shared_permission_id');
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->text('name');
        });
        Schema::create('transactions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('account_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('category_id')->nullable();
            $t->string('type');
            $t->decimal('amount', 15, 2);
            $t->date('date');
            $t->string('status');
            $t->text('description')->nullable();
        });
        DB::table('currencies')->insert([['id' => 1, 'code' => 'EUR'], ['id' => 2, 'code' => 'USD']]);
        foreach ([1, 2, 3, 4, 5] as $id) {
            DB::table('accounts')->insert(['id' => $id, 'name' => 'Account '.$id, 'type' => 'bank_account', 'active' => $id !== 2, 'balance' => $id === 4 ? 50 : 100, 'currency_id' => $id === 4 ? 2 : 1]);
        }
        DB::table('account_users')->insert([
            ['account_id' => 1, 'user_id' => 1, 'shared_role_id' => 1],
            ['account_id' => 2, 'user_id' => 1, 'shared_role_id' => 1],
            ['account_id' => 2, 'user_id' => 2, 'shared_role_id' => 1],
            ['account_id' => 3, 'user_id' => 11, 'shared_role_id' => 1],
            ['account_id' => 4, 'user_id' => 1, 'shared_role_id' => 1],
            ['account_id' => 5, 'user_id' => 1, 'shared_role_id' => 2],
        ]);
        DB::table('shared_permissions')->insert([['id' => 1, 'code' => 'viewAccount'], ['id' => 2, 'code' => 'editAccount']]);
        DB::table('shared_permission_roles')->insert([
            ['shared_role_id' => 1, 'shared_permission_id' => 1], ['shared_role_id' => 1, 'shared_permission_id' => 2],
            ['shared_role_id' => 2, 'shared_permission_id' => 2],
        ]);
        DB::table('categories')->insert(['id' => 1, 'name' => json_encode(['pt' => 'Compras', 'en' => 'Shopping'])]);
        $user = (new User)->forceFill(['id' => 1, 'name' => 'Reader']);
        $user->setRelation('preferences', (object) ['currency' => (new Currency)->forceFill(['code' => 'EUR'])]);
        $this->actingAs($user);
        $this->item(['type' => 'revenue', 'amount' => 1000]);
        $this->item(['account_id' => 2, 'user_id' => 2, 'category_id' => 1, 'amount' => 250]);
        $this->item(['date' => '2026-09-05', 'amount' => 50]);
        $this->item(['date' => '2026-07-05', 'amount' => 75]);
        $this->item(['status' => 'pending', 'amount' => 30]);
        $this->item(['date' => '2026-11-01', 'amount' => 9999]);
        $this->item(['account_id' => 3, 'user_id' => 11, 'amount' => 8999]);
        $this->item(['account_id' => 5, 'amount' => 500]);
        $this->item(['account_id' => 4, 'amount' => 20]);
    }

    private function item(array $values): void
    {
        DB::table('transactions')->insert(array_replace(['account_id' => 1, 'user_id' => 1, 'type' => 'expense', 'amount' => 10, 'date' => '2026-10-05', 'status' => 'completed'], $values));
    }

    public function test_resume_scopes_permissions_membership_currency_and_status_without_duplicates(): void
    {
        $this->getJson('/api/v1/account/resume?period=3m')->assertOk()
            ->assertJsonPath('data.currencyCode', 'EUR')->assertJsonPath('data.currencies', ['EUR', 'USD'])
            ->assertJsonPath('data.summary.totalAccounts', 2)->assertJsonPath('data.summary.activeAccounts', 1)
            ->assertJsonPath('data.summary.balance', 200)->assertJsonPath('data.summary.income', 1000)
            ->assertJsonPath('data.summary.expenses', 300)->assertJsonPath('data.summary.net', 700)
            ->assertJsonPath('data.summary.savingsRate', 70)->assertJsonPath('data.summary.averageDailyExpense', 3.26)
            ->assertJsonPath('data.summary.completedCount', 3)->assertJsonPath('data.summary.pendingCount', 1)
            ->assertJsonCount(2, 'data.accounts')->assertJsonCount(4, 'data.latestActivity')
            ->assertJsonPath('data.categories.0.name', 'Compras')->assertJsonPath('data.categories.1.name', null)
            ->assertJsonMissing(['amount' => 9999])->assertJsonMissing(['amount' => 8999])->assertJsonMissing(['amount' => 500]);
    }

    public function test_monthly_series_fills_gaps_and_all_time_and_ytd_use_correct_boundaries(): void
    {
        $this->getJson('/api/v1/account/resume?period=3m')->assertJsonPath('data.period.from', '2026-08-01')
            ->assertJsonCount(3, 'data.monthly')->assertJsonPath('data.monthly.0.month', '2026-08')
            ->assertJsonPath('data.monthly.0.expenses', 0)->assertJsonPath('data.monthly.1.expenses', 50)
            ->assertJsonPath('data.monthly.2.expenses', 250);
        $this->getJson('/api/v1/account/resume?period=all')->assertJsonPath('data.period.from', '2026-07-01')->assertJsonPath('data.summary.expenses', 375);
        $this->getJson('/api/v1/account/resume?period=ytd')->assertJsonPath('data.period.from', '2026-01-01')->assertJsonCount(10, 'data.monthly');
        $this->getJson('/api/v1/account/resume')->assertJsonPath('data.period.from', '2025-11-01')->assertJsonCount(12, 'data.monthly');
    }

    public function test_currencies_never_mix_and_no_income_has_no_savings_percentage(): void
    {
        $this->getJson('/api/v1/account/resume?period=3m&currency=USD')->assertOk()
            ->assertJsonPath('data.summary.balance', 50)->assertJsonPath('data.summary.expenses', 20)
            ->assertJsonPath('data.summary.income', 0)->assertJsonPath('data.summary.savingsRate', null)
            ->assertJsonCount(1, 'data.accounts')->assertJsonCount(1, 'data.latestActivity');
    }

    public function test_empty_accounts_and_empty_transactions_have_explicit_empty_contracts(): void
    {
        DB::table('transactions')->delete();
        $this->getJson('/api/v1/account/resume')->assertOk()->assertJsonPath('data.summary.balance', 200)
            ->assertJsonPath('data.summary.completedCount', 0)->assertJsonCount(0, 'data.categories')->assertJsonCount(0, 'data.latestActivity');
        DB::table('account_users')->where('user_id', 1)->delete();
        $this->getJson('/api/v1/account/resume')->assertOk()->assertJsonPath('data.summary.totalAccounts', 0)
            ->assertJsonPath('data.summary.balance', 0)->assertJsonPath('data.summary.savingsRate', null)
            ->assertJsonCount(0, 'data.accounts')->assertJsonCount(0, 'data.currencies');
    }

    public function test_invalid_filters_and_inaccessible_currencies_are_rejected(): void
    {
        foreach (['period=bad', 'period[]=3m', 'currency=GBP', 'currency=eur', 'currency[]=EUR'] as $query) {
            $this->getJson('/api/v1/account/resume?'.$query)->assertUnprocessable();
        }
    }

    public function test_activity_is_bounded_ordered_and_negative_balances_are_preserved(): void
    {
        DB::table('accounts')->where('id', 1)->update(['balance' => -150]);
        for ($day = 6; $day <= 20; $day++) {
            $this->item(['date' => '2026-10-'.$day]);
        }
        $this->getJson('/api/v1/account/resume')->assertOk()->assertJsonCount(8, 'data.latestActivity')
            ->assertJsonPath('data.latestActivity.0.date', '2026-10-20')->assertJsonPath('data.summary.balance', -50);
    }

    public function test_authentication_is_required(): void
    {
        $this->withMiddleware();
        auth()->forgetGuards();
        $this->getJson('/api/v1/account/resume')->assertUnauthorized();
    }

    public function test_service_failure_returns_a_safe_error_without_query_details(): void
    {
        $this->mock(AccountResumeRepository::class)
            ->shouldReceive('get')->once()->andThrow(new \RuntimeException('SQL private details'));
        $this->getJson('/api/v1/account/resume')->assertStatus(500)
            ->assertJsonPath('success', false)->assertJsonPath('errors', null)
            ->assertDontSee('SQL private details');
    }
}
