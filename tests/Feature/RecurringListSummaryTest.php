<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Modules\User\Entities\User;
use Tests\TestCase;

class RecurringListSummaryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::connection()->getPdo()->sqliteCreateFunction('FIND_IN_SET',
            fn ($id, $members) => in_array((string) $id, explode(',', $members), true) ? 1 : 0, 2);
        Gate::before(fn () => true);
        $this->travelTo(Carbon::parse('2026-10-31 12:00:00'));

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('symbol');
        });
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->unsignedBigInteger('currency_id');
        });
        Schema::create('accounts_view', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('user_ids');
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('frequency');
            $table->integer('custom_frequency_days')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_linked')->default(false);
            $table->string('linked_type')->nullable();
            $table->bigInteger('linked_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('users')->insert([['id' => 1, 'name' => 'Reader'], ['id' => 2, 'name' => 'Other member'], ['id' => 11, 'name' => 'Private']]);
        DB::table('currencies')->insert([['id' => 1, 'code' => 'EUR', 'symbol' => '€'], ['id' => 2, 'code' => 'USD', 'symbol' => '$']]);
        DB::table('accounts')->insert([
            ['id' => 1, 'name' => 'Personal', 'type' => 'bank', 'currency_id' => 1],
            ['id' => 2, 'name' => 'Shared', 'type' => 'bank', 'currency_id' => 2],
            ['id' => 3, 'name' => 'Private', 'type' => 'bank', 'currency_id' => 1],
        ]);
        DB::table('accounts_view')->insert([['id' => 1, 'user_ids' => '1'], ['id' => 2, 'user_ids' => '2, 1'], ['id' => 3, 'user_ids' => '11']]);
        $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Reader'])->setRelation('preferences', null));

        for ($id = 1; $id <= 15; $id++) {
            $this->item(['title' => 'Visible '.$id]);
        }
        $this->item(['account_id' => 2, 'user_id' => 2, 'title' => 'Shared income', 'type' => 'revenue', 'frequency' => 'yearly', 'amount' => 120]);
        $this->item(['title' => 'Paused', 'is_active' => false, 'amount' => 999]);
        $this->item(['account_id' => 3, 'user_id' => 11, 'title' => 'Private', 'amount' => 999]);
        $this->item(['title' => 'Deleted', 'deleted_at' => now(), 'amount' => 999]);
    }

    private function item(array $overrides): void
    {
        DB::table('recurring_transactions')->insert(array_replace([
            'account_id' => 1, 'user_id' => 1, 'title' => 'Example', 'type' => 'expense',
            'amount' => 10, 'frequency' => 'monthly', 'start_date' => '2026-10-31',
        ], $overrides));
    }

    public function test_one_paginated_response_contains_the_full_visible_summary(): void
    {
        $response = $this->getJson('/api/v1/recurrings?includeSummary=1&start=0&length=2')
            ->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('summary.activeCount', 16)
            ->assertJsonPath('summary.pausedRecurring', 1)
            ->assertJsonPath('summary.incomeCount', 1)
            ->assertJsonPath('summary.expenseCount', 15)
            ->assertJsonCount(5, 'summary.upcoming')
            ->assertJsonPath('recordsFiltered', 17);

        $this->assertEquals([
            ['currencyCode' => 'EUR', 'monthlyIncome' => 0, 'monthlyExpenses' => 150],
            ['currencyCode' => 'USD', 'monthlyIncome' => 10, 'monthlyExpenses' => 0],
        ], $response->json('summary.currencySummaries'));
        $this->assertArrayNotHasKey('account', $response->json('summary.upcoming.0'));
        $this->assertArrayNotHasKey('user', $response->json('summary.upcoming.0'));
        $response->assertJsonMissing(['title' => 'Private'])->assertJsonMissing(['title' => 'Deleted']);
    }

    public function test_filters_and_later_pages_do_not_change_global_totals(): void
    {
        foreach ([['isActive' => ['eq' => 0]], ['search' => ['value' => 'NoSuchTitle']], ['start' => 10]] as $filter) {
            $params = array_replace([
                'includeSummary' => 1, 'start' => 0, 'length' => 2,
                'columns' => [['data' => 'title', 'name' => 'recurring_transactions.title', 'searchable' => 'true']],
            ], $filter);
            $response = $this->getJson('/api/v1/recurrings?'.http_build_query($params))
                ->assertOk()->assertJsonPath('summary.activeCount', 16)
                ->assertJsonPath('summary.pausedRecurring', 1);
            $response->assertJsonPath('recordsFiltered', isset($filter['isActive']) ? 1 : (isset($filter['search']) ? 0 : 17));
        }
    }

    public function test_summary_is_opt_in_for_other_list_consumers(): void
    {
        foreach (['', '&includeSummary=0'] as $flag) {
            $this->getJson('/api/v1/recurrings?start=0&length=2'.$flag)
                ->assertOk()->assertJsonCount(2, 'data')->assertJsonMissingPath('summary');
        }
    }

    public function test_a_user_without_visible_accounts_receives_an_empty_summary(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 4, 'name' => 'No accounts'])->setRelation('preferences', null));
        $this->getJson('/api/v1/recurrings?includeSummary=1&start=0&length=2')
            ->assertOk()->assertJsonCount(0, 'data')
            ->assertJsonPath('summary.activeCount', 0)
            ->assertJsonPath('summary.pausedRecurring', 0)
            ->assertJsonCount(0, 'summary.currencySummaries')
            ->assertJsonCount(0, 'summary.upcoming');
    }
}
