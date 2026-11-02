<?php

namespace Modules\RecurringTransaction\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\RecurringTransaction\Entities\RecurringTransaction;

class RecurringSummaryRepository
{
    public function forUser(int $userId): array
    {
        // Match the table's account membership boundary, without its filters or pagination.
        $items = RecurringTransaction::query()
            ->join('accounts_view as av', 'av.id', '=', 'recurring_transactions.account_id')
            ->whereRaw("FIND_IN_SET(?, REPLACE(av.user_ids, ' ', ''))", [$userId])
            ->select('recurring_transactions.*')
            ->with(['account.currency', 'linked'])
            ->get();

        return $this->summarize($items, now()->startOfDay());
    }

    private function isReservation($item): bool
    {
        return $item->is_linked && $item->linked_type === \Modules\FinancialGoal\Entities\FinancialGoal::class
            && $item->linked?->funding_mode === 'reservation';
    }

    public function summarize(Collection $items, Carbon $today): array
    {
        $active = $items->filter(fn ($item) => $item->is_active);
        $currencies = [];
        foreach ($active as $item) {
            $code = $item->account->currency->code;
            $currencies[$code] ??= ['currencyCode' => $code, 'monthlyIncome' => 0.0, 'monthlyExpenses' => 0.0];
            $amount = (float) $item->amount;
            $monthly = match ($item->frequency) {
                'daily' => $amount * 30,
                'weekly' => $amount * 52 / 12,
                'monthly' => $amount,
                'yearly' => $amount / 12,
                'custom' => $item->custom_frequency_days > 0 ? $amount * 30 / $item->custom_frequency_days : 0,
                default => 0,
            };
            $key = $this->isReservation($item)
                ? ($item->type === 'revenue' ? 'monthlyReleases' : 'monthlyReservations')
                : ($item->type === 'revenue' ? 'monthlyIncome' : 'monthlyExpenses');
            $currencies[$code][$key] ??= 0.0;
            $currencies[$code][$key] += $monthly;
        }
        $end = $today->copy()->addDays(7)->toDateString();
        $upcoming = $active->filter(fn ($item) => $item->next_occurrence && $item->next_occurrence <= $end)
            ->sortBy('next_occurrence')->take(5)->map(fn ($item) => [
                ...($this->isReservation($item) ? ['movementKind' => $item->type === 'revenue' ? 'release' : 'reservation'] : []),
                'id' => $item->id, 'title' => $item->title, 'type' => $item->type,
                'amount' => (float) $item->amount, 'currencyCode' => $item->account->currency->code,
                'nextOccurrence' => $item->next_occurrence,
            ])->values()->all();

        return [
            'activeCount' => $active->count(), 'pausedRecurring' => $items->count() - $active->count(),
            'incomeCount' => $active->filter(fn ($item) => ! $this->isReservation($item))->where('type', 'revenue')->count(),
            'expenseCount' => $active->filter(fn ($item) => ! $this->isReservation($item))->where('type', 'expense')->count(),
            'currencySummaries' => array_values($currencies), 'upcoming' => $upcoming,
        ];
    }
}
