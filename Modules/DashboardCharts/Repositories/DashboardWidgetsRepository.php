<?php

namespace Modules\DashboardCharts\Repositories;

use Illuminate\Http\Request;
use Modules\Accounts\Repositories\AccountRepository;
use Modules\Debts\Entities\DebtsView;
use Modules\FinancialGoal\Entities\FinancialGoalView;
use Modules\RecurringTransaction\Repositories\RecurringSummaryRepository;

class DashboardWidgetsRepository
{
    public function __construct(
        protected AccountRepository $accounts,
        protected RecurringSummaryRepository $recurring,
    ) {}

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

        return [
            'accounts' => $this->accounts->getStats($request),
            'debts' => $debts,
            'goals' => $goals,
            'recurring' => $this->recurring->forUser($userId),
        ];
    }
}
