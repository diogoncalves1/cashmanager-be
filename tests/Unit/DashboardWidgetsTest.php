<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Repositories\AccountRepository;
use Modules\Currency\Entities\Currency;
use Modules\DashboardCharts\Repositories\DashboardWidgetsRepository;
use Modules\RecurringTransaction\Entities\RecurringTransaction;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    private function item(array $attributes = [], string $currency = 'EUR'): RecurringTransaction
    {
        $account = (new Account)->setRelation('currency', new Currency(['code' => $currency]));

        return (new RecurringTransaction(array_merge([
            'amount' => '10.50', 'title' => 'Example', 'frequency' => 'monthly',
            'type' => 'expense', 'is_active' => true, 'start_date' => '2026-10-20',
        ], $attributes)))->setRelation('account', $account);
    }

    private function summary(Collection $items): array
    {
        $repository = new DashboardWidgetsRepository(Mockery::mock(AccountRepository::class));

        return $repository->summarizeRecurring($items, Carbon::parse('2026-10-21'));
    }

    public function test_currency_totals_are_numeric_and_never_mix_currencies_or_paused_items(): void
    {
        $summary = $this->summary(collect([
            $this->item(), $this->item(['amount' => 120, 'frequency' => 'yearly', 'type' => 'revenue']),
            $this->item(['amount' => 20], 'USD'), $this->item(['amount' => 999, 'is_active' => false]),
        ]));
        $this->assertSame(3, $summary['activeCount']);
        $this->assertSame(1, $summary['pausedRecurring']);
        $this->assertSame(1, $summary['incomeCount']);
        $this->assertSame(2, $summary['expenseCount']);
        $this->assertSame([
            ['currencyCode' => 'EUR', 'monthlyIncome' => 10.0, 'monthlyExpenses' => 10.5],
            ['currencyCode' => 'USD', 'monthlyIncome' => 0.0, 'monthlyExpenses' => 20.0],
        ], $summary['currencySummaries']);
    }

    public function test_upcoming_payload_is_bounded_but_totals_cover_every_visible_item(): void
    {
        $items = collect(range(1, 12))->map(fn ($day) => $this->item(['start_date' => sprintf('2026-10-%02d', $day)]));
        $summary = $this->summary($items);
        $this->assertCount(5, $summary['upcoming']);
        $this->assertSame('2026-10-01', $summary['upcoming'][0]['nextOccurrence']);
        $this->assertSame(12, $summary['activeCount']);
        $this->assertSame(126.0, $summary['currencySummaries'][0]['monthlyExpenses']);
        $this->assertArrayNotHasKey('account', $summary['upcoming'][0]);
        $this->assertArrayNotHasKey('user', $summary['upcoming'][0]);
    }

    public function test_paused_future_and_finished_schedules_are_not_presented_as_due(): void
    {
        $summary = $this->summary(collect([
            $this->item(['start_date' => '2026-11-01']),
            $this->item(['is_active' => false]),
            $this->item(['start_date' => '2026-09-01', 'last_generated_at' => '2026-10-01', 'end_date' => '2026-10-20']),
            $this->item(['start_date' => '2026-10-28']),
        ]));
        $this->assertCount(1, $summary['upcoming']);
        $this->assertSame('2026-10-28', $summary['upcoming'][0]['nextOccurrence']);
    }

    public function test_daily_weekly_and_custom_monthly_estimates_match_the_schedule(): void
    {
        $summary = $this->summary(collect([
            $this->item(['amount' => 1, 'frequency' => 'daily']),
            $this->item(['amount' => 12, 'frequency' => 'weekly']),
            $this->item(['amount' => 5, 'frequency' => 'custom', 'custom_frequency_days' => 5]),
        ]));
        $this->assertSame(112.0, $summary['currencySummaries'][0]['monthlyExpenses']);
    }
}
