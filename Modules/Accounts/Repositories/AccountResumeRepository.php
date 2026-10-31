<?php

namespace Modules\Accounts\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\Transaction;
use Modules\Category\Entities\Category;

class AccountResumeRepository
{
    public function get(Request $request): array
    {
        // A membership subquery avoids multiplying shared accounts or equal balances.
        $accounts = Account::with('currency')->whereIn('id', DB::table('account_users as membership')
            ->join('shared_permission_roles as permissions', 'permissions.shared_role_id', '=', 'membership.shared_role_id')
            ->join('shared_permissions', 'shared_permissions.id', '=', 'permissions.shared_permission_id')
            ->select('membership.account_id')->where('membership.user_id', $request->user()->id)
            ->where('shared_permissions.code', 'viewAccount'))->orderBy('name')->get();
        $currencies = $accounts->pluck('currency.code')->filter()->unique()->sort()->values();
        $preferred = $request->user()->preferences?->currency?->code ?? 'EUR';
        $currency = $request->input('currency') ?? ($currencies->contains($preferred) ? $preferred : ($currencies->first() ?? $preferred));
        if ($request->filled('currency') && ! $currencies->contains($currency)) {
            throw ValidationException::withMessages(['currency' => __('validation.in', ['attribute' => 'currency'])]);
        }
        $accounts = $accounts->filter(fn ($account) => $account->currency->code === $currency)->values();
        $ids = $accounts->pluck('id');
        $today = CarbonImmutable::today();
        $period = $request->input('period', '12m');
        $base = Transaction::query()->whereIn('account_id', $ids)->whereDate('date', '<=', $today->toDateString());
        $from = match ($period) {
            '3m' => $today->startOfMonth()->subMonths(2),
            'ytd' => $today->startOfYear(),
            'all' => CarbonImmutable::parse((clone $base)->min('date') ?? $today)->startOfMonth(),
            default => $today->startOfMonth()->subMonths(11),
        };
        $movements = (clone $base)->whereDate('date', '>=', $from->toDateString());
        $completed = (clone $movements)->where('status', 'completed');
        $totals = (clone $completed)->selectRaw("SUM(CASE WHEN type = 'revenue' THEN amount ELSE 0 END) AS income, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expenses, COUNT(*) AS count")->first();
        $income = round((float) $totals->income, 2);
        $expenses = round((float) $totals->expenses, 2);
        $grouped = (clone $completed)->selectRaw("SUBSTR(date, 1, 7) AS month, SUM(CASE WHEN type = 'revenue' THEN amount ELSE 0 END) AS revenues, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expenses")
            ->groupByRaw('SUBSTR(date, 1, 7)')->get()->keyBy('month');
        $monthly = [];
        for ($month = $from; $month <= $today; $month = $month->addMonth()) {
            $key = $month->format('Y-m');
            $row = $grouped->get($key);
            $monthly[] = ['month' => $key, 'revenues' => round((float) ($row?->revenues ?? 0), 2), 'expenses' => round((float) ($row?->expenses ?? 0), 2)];
        }
        $byAccount = (clone $completed)->selectRaw("account_id, COUNT(*) AS count, SUM(CASE WHEN type = 'revenue' THEN amount ELSE 0 END) AS revenues, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expenses")
            ->groupBy('account_id')->get()->keyBy('account_id');
        $categoryTotals = (clone $completed)->where('type', 'expense')->selectRaw('category_id, SUM(amount) AS amount')
            ->groupBy('category_id')->orderByDesc('amount')->get();
        $categoryNames = Category::whereIn('id', $categoryTotals->pluck('category_id')->filter())->get()->keyBy('id');

        return [
            'generatedAt' => now()->toIso8601String(),
            'currencyCode' => $currency,
            'currencies' => $currencies->all(),
            'period' => ['key' => $period, 'from' => $from->toDateString(), 'to' => $today->toDateString()],
            'summary' => [
                'totalAccounts' => $accounts->count(),
                'activeAccounts' => $accounts->where('active', true)->count(),
                'balance' => round((float) $accounts->sum('balance'), 2),
                'income' => $income,
                'expenses' => $expenses,
                'net' => round($income - $expenses, 2),
                'savingsRate' => $income > 0 ? round(($income - $expenses) / $income * 100, 1) : null,
                'averageDailyExpense' => round($expenses / max(1, $from->diffInDays($today) + 1), 2),
                'completedCount' => (int) $totals->count,
                'pendingCount' => (clone $movements)->where('status', 'pending')->count(),
            ],
            'monthly' => $monthly,
            'accounts' => $accounts->map(function ($account) use ($byAccount) {
                $row = $byAccount->get($account->id);

                return ['id' => (string) $account->id, 'name' => $account->name, 'type' => $account->type,
                    'active' => (bool) $account->active, 'balance' => round((float) $account->balance, 2),
                    'revenues' => round((float) ($row?->revenues ?? 0), 2), 'expenses' => round((float) ($row?->expenses ?? 0), 2),
                    'transactionCount' => (int) ($row?->count ?? 0)];
            })->all(),
            'categories' => $categoryTotals->map(fn ($row) => [
                'id' => $row->category_id === null ? null : (string) $row->category_id,
                'name' => $this->categoryName($categoryNames->get($row->category_id)),
                'amount' => round((float) $row->amount, 2),
            ])->all(),
            'latestActivity' => (clone $movements)->whereIn('status', ['completed', 'pending'])->with(['account', 'category'])
                ->orderByDesc('date')->orderByDesc('id')->limit(8)->get()->map(fn ($item) => [
                    'id' => (string) $item->id, 'description' => $item->description,
                    'type' => $item->type, 'amount' => round((float) $item->amount, 2), 'date' => substr($item->date, 0, 10),
                    'status' => $item->status, 'categoryName' => $this->categoryName($item->category),
                    'account' => ['id' => (string) $item->account_id, 'name' => $item->account->name],
                ])->all(),
        ];
    }

    private function categoryName(?Category $category): ?string
    {
        if (! $category) {
            return null;
        }
        $name = $category->name;
        if (is_string($name)) {
            return $name;
        }

        return $name->{app()->getLocale()} ?? $name->en ?? null;
    }
}
