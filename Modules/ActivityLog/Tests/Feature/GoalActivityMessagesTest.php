<?php

namespace Modules\ActivityLog\Tests\Feature;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Accounts\Core\Helpers;
use Modules\ActivityLog\Core\ActivityLogMessageResolver;
use Modules\FinancialGoal\Entities\FinancialGoalView;
use Modules\FinancialGoal\Repositories\FinancialGoalRepository;
use Tests\Support\ListingTestCase;

class GoalActivityMessagesTest extends ListingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('financial_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id');
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->json('metadata');
            $table->timestamps();
        });
        DB::table('financial_goals')->insert(['id' => 9, 'currency_id' => 1]);
    }

    public function test_goal_activity_endpoint_reads_new_and_historical_events_without_required_fallbacks(): void
    {
        $metadata = [
            ['type' => 'financial_transaction_added', 'transactionType' => 'contribution'],
            ['type' => 'financial_transaction_added', 'transactionType' => 'withdrawal'],
            ['type' => 'financial_transaction_added', 'transactionType' => 'usage'],
            ['type' => 'financial_transaction_scheduled', 'transactionType' => 'contribution'],
            ['type' => 'financial_transaction_deleted'],
            ['type' => 'financial_transaction_confirmed'],
            ['type' => 'goal_created', 'initialTarget' => 100],
            ['type' => 'financial_transaction_added', 'transactionType' => 'contribution', 'amountFallback' => 'old snapshot'],
        ];
        foreach ($metadata as $event) {
            DB::table('activity_logs')->insert(['subject_id' => 9, 'user_id' => 1, 'type' => 'financial_goal',
                'metadata' => json_encode($event + ['goalId' => 9, 'amount' => 100, 'date' => '2026-11-21']),
                'created_at' => '2026-11-21 12:00:00']);
        }
        $this->mock(FinancialGoalRepository::class)->shouldReceive('showToUser')->twice()
            ->withArgs(fn ($request, $id) => $id === '9')->andReturn(new FinancialGoalView);
        foreach (['pt', 'en'] as $locale) {
            app()->setLocale($locale);
            $response = $this->getJson('/api/v1/financial-goals/9/activity')->assertOk()->assertJsonPath('pagination.total', 8);
            foreach ($response->json('data') as $row) {
                $this->assertStringContainsString(Helpers::formatMoneyWithCurrency(100, 'EUR', '€'), $row['message']);
                $this->assertStringNotContainsString('activitylog::', $row['message']);
                $this->assertStringNotContainsString(':amount', $row['message']);
                $this->assertArrayNotHasKey('metadata', $row);
            }
        }
    }

    public function test_missing_goal_or_currency_uses_the_snapshot_or_raw_amount_without_an_error(): void
    {
        $resolver = app(ActivityLogMessageResolver::class);
        $event = ['type' => 'financial_transaction_added', 'transactionType' => 'usage', 'goalId' => 99,
            'amount' => '12.34', 'date' => '2026-11-21'];
        $this->assertSame('12.34', $resolver->resolve('financial_goal', $event)['params']['amount']);
        $this->assertSame('12.34 EUR', $resolver->resolve('financial_goal', $event + ['amountFallback' => '12.34 EUR'])['params']['amount']);
        $this->assertSame(Helpers::formatMoneyWithCurrency(12.34, 'EUR', '€'), $resolver->resolve('financial_goal', $event + ['currencyId' => 1])['params']['amount']);
        DB::table('currencies')->where('id', 1)->delete();
        $event['goalId'] = 9;
        $this->assertSame('12.34 EUR', $resolver->resolve('financial_goal', $event + ['amountFallback' => '12.34 EUR'])['params']['amount']);
        $this->assertSame('12.34', $resolver->resolve('financial_goal', $event)['params']['amount']);
        $created = ['type' => 'goal_created', 'goalId' => 99, 'initialTarget' => '25.00', 'initialTargetFallback' => '25.00 EUR'];
        $this->assertSame('25.00 EUR', $resolver->resolve('financial_goal', $created)['params']['initialTarget']);
    }

    public function test_goal_activity_still_requires_subject_authorization(): void
    {
        $this->mock(FinancialGoalRepository::class)->shouldReceive('showToUser')->andThrow(new AuthorizationException);
        $this->getJson('/api/v1/financial-goals/9/activity')->assertForbidden();
    }
}
