<?php

namespace Modules\DashboardCharts\Repositories;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Accounts\Repositories\AccountRepository;
use Modules\Debts\Entities\DebtsView;
use Modules\FinancialGoal\Entities\FinancialGoalView;
use Modules\RecurringTransaction\Entities\RecurringTransaction;

class DashboardWidgetsRepository
{
    public function __construct(protected AccountRepository $accounts) {}

    public function get(Request $request): array
    {
        $userId = $request->user()->id;

        // Use the same membership boundary as each domain's list endpoint.
        $debts = DebtsView::query()
            ->whereRaw("FIND_IN_SET(?, REPLACE(userIds, ' ', ''))", [$userId])
            ->status('pending')->orderBy('dueDate')->orderBy('id')->limit(3)
            ->get(['id', 'name', 'totalAmount', 'paidAmount', 'currencyCode', 'status', 'dueDate'])
            ->map(fn ($debt) => [
                'id' => $debt->id, 'name' => $debt->name,
                'totalAmount' => (float) $debt->totalAmount, 'paidAmount' => (float) $debt->paidAmount,
                'currencyCode' => $debt->currencyCode, 'status' => $debt->status,
                'statusTranslated' => __('debts::attributes.debts.status.'.$debt->status),
                'dueDate' => $debt->dueDate,
            ])->all();

        $goals = FinancialGoalView::query()
            ->whereRaw("FIND_IN_SET(?, REPLACE(userIds, ' ', ''))", [$userId])
            ->status('in_progress')
            ->orderByRaw('CASE WHEN totalAmount > 0 THEN contributedAmount / totalAmount ELSE 0 END DESC')
            ->orderBy('id')->limit(3)
            ->get(['id', 'name', 'totalAmount', 'contributedAmount', 'currencyCode', 'status'])
            ->map(fn ($goal) => [
                'id' => $goal->id, 'name' => $goal->name,
                'totalAmount' => (float) $goal->totalAmount,
                'contributedAmount' => (float) $goal->contributedAmount,
                'currencyCode' => $goal->currencyCode, 'status' => $goal->status,
                'statusTranslated' => __('financialgoal::attributes.financial-goals.status.'.$goal->status),
            ])->all();

        $recurring = RecurringTransaction::query()
            ->join('accounts_view as av', 'av.id', '=', 'recurring_transactions.account_id')
            ->whereRaw("FIND_IN_SET(?, REPLACE(av.user_ids, ' ', ''))", [$userId])
            ->select('recurring_transactions.*')->with('account.currency')->get();

        return [
            'accounts' => $this->accounts->getStats($request),
            'debts' => $debts,
            'goals' => $goals,
            'recurring' => $this->summarizeRecurring($recurring, now()->startOfDay()),
        ];
    }

    public function summarizeRecurring(Collection $items, Carbon $today): array
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
            $key = $item->type === 'revenue' ? 'monthlyIncome' : 'monthlyExpenses';
            $currencies[$code][$key] += $monthly;
        }
        $end = $today->copy()->addDays(7)->toDateString();
        $upcoming = $active->filter(fn ($item) => $item->next_occurrence && $item->next_occurrence <= $end)
            ->sortBy('next_occurrence')->take(5)->map(fn ($item) => [
                'id' => $item->id, 'title' => $item->title, 'type' => $item->type,
                'amount' => (float) $item->amount, 'currencyCode' => $item->account->currency->code,
                'nextOccurrence' => $item->next_occurrence,
            ])->values()->all();

        return [
            'activeCount' => $active->count(), 'pausedRecurring' => $items->count() - $active->count(),
            'incomeCount' => $active->where('type', 'revenue')->count(),
            'expenseCount' => $active->where('type', 'expense')->count(),
            'currencySummaries' => array_values($currencies), 'upcoming' => $upcoming,
        ];
    }
}
