<?php

namespace Modules\FinancialGoal\Services;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\Transaction;
use Modules\Accounts\Repositories\TransactionRepository;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Modules\Category\Rules\SelectableCategory;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\User\Entities\User;

class GoalReservations
{
    public static function cents(mixed $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    public static function reserved(int|string $accountId, int|string|null $goalId = null, bool $currentRead = false): float
    {
        if (! Schema::hasColumn('financial_goals', 'funding_mode')) {
            return 0;
        }
        // Direct account IDs are populated only for new/explicitly converted reservations.
        $query = FinancialGoalTransaction::where('account_id', $accountId)->where('status', 'completed')
            ->when($goalId, fn ($query) => $query->where('financial_goal_id', $goalId));
        if ($currentRead) {
            // A locking read sees the latest committed rows even after a transaction
            // established a repeatable-read snapshot before waiting for an account lock.
            return $query->lockForUpdate()->get(['type', 'amount'])->sum(fn ($entry) => self::cents($entry->amount) * ($entry->type === 'contribution' ? 1 : -1)
            ) / 100;
        }

        return round((float) $query->selectRaw("COALESCE(SUM(CASE WHEN type = 'contribution' THEN amount ELSE -amount END), 0) AS reserved")->value('reserved'), 2);
    }

    public static function accountSummary(Account $account): array
    {
        $reserved = self::reserved($account->id);
        $available = (self::cents($account->balance) - self::cents($reserved)) / 100;

        return ['balance' => (float) $account->balance, 'reservedAmount' => $reserved,
            'availableAmount' => $available, 'reservationShortfall' => max(0, -$available)];
    }

    public static function goalSummary(FinancialGoal $goal): array
    {
        $used = (float) $goal->transactions()->where('type', 'usage')->where('status', 'completed')->sum('amount');
        $funded = (float) $goal->contributed_amount;

        return ['fundingMode' => $goal->funding_mode ?? 'legacy',
            'reservedAmount' => $goal->funding_mode === 'reservation' ? round($funded - $used, 2) : null,
            'usedAmount' => $used, 'fundedAmount' => $funded,
            'completedFunding' => $goal->completed_funding === null ? null : (float) $goal->completed_funding];
    }

    public static function expenseGoals(int|string $transactionId, int|string $userId): array
    {
        if (! Schema::hasColumn('financial_goals', 'funding_mode')) {
            return [];
        }

        return FinancialGoalTransaction::with('financialGoal')->where('transaction_id', $transactionId)->where('type', 'usage')->get()
            ->filter(fn ($entry) => $entry->financialGoal->userSharedRole($entry->financialGoal, $userId)?->hasPermission('viewFinancialGoal'))
            ->map(fn ($entry) => ['id' => (string) $entry->financial_goal_id, 'name' => $entry->financialGoal->name, 'amount' => (float) $entry->amount])->values()->all();
    }

    public static function permission($subject, int|string $userId, string $permission): void
    {
        abort_unless($subject->userSharedRole($subject, $userId)?->hasPermission($permission), 403);
    }

    public static function invalid(string $field, string $key): never
    {
        throw ValidationException::withMessages([$field => __('financialgoal::reservations.'.$key)]);
    }

    private function sync(FinancialGoal $goal): void
    {
        $goal->contributed_amount = $goal->transactions()->where('status', 'completed')->lockForUpdate()->get(['type', 'amount'])
            ->sum(fn ($entry) => self::cents($entry->amount) * match ($entry->type) {
                'contribution' => 1, 'withdrawal' => -1, default => 0
            }) / 100;
        $goal->save();
    }

    public function store(Request $request): FinancialGoalTransaction
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            // Category mutations use the same user-first lock order.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $account = Account::whereKey($request->input('account_id'))->lockForUpdate()->firstOrFail();
            $goal = FinancialGoal::whereKey($request->input('financial_goal_id'))->lockForUpdate()->firstOrFail();
            self::permission($goal, $user->id, 'storeFinancialGoalTransaction');
            self::permission($account, $user->id, 'createTransaction');
            if ($goal->funding_mode !== 'reservation') {
                self::invalid('financial_goal_id', 'legacy');
            }
            if ((string) $goal->currency_id !== (string) $account->currency_id) {
                self::invalid('account_id', 'currency');
            }
            if (! $account->active && $request->input('type') !== 'withdrawal') {
                self::invalid('account_id', 'inactive');
            }
            $data = $request->only(['financial_goal_id', 'account_id', 'amount', 'date', 'type', 'status', 'description', 'request_key']);
            $data['user_id'] = $user->id;
            $recurringId = $request->boolean('is_recurring') ? $request->input('recurring_transaction_id') : null;
            // HTTP requests never accept recurrence identities; only the generator's plain Request supplies them.
            if ($request instanceof FormRequest) {
                $recurringId = null;
            }
            $hash = hash('sha256', json_encode([$data, $request->input('transaction_id'), $request->input('category_id')], JSON_PRESERVE_ZERO_FRACTION));
            if (! empty($data['request_key'])) {
                $receipt = DB::table('financial_goal_request_receipts')->where('request_key', $data['request_key'])->first();
                if ($receipt) {
                    if ((string) $receipt->financial_goal_id !== (string) $goal->id || (string) $receipt->user_id !== (string) $user->id || $receipt->request_hash !== $hash) {
                        self::invalid('request_key', 'retry');
                    }
                    $replayed = FinancialGoalTransaction::find($receipt->entry_id);
                    if (! $replayed) {
                        self::invalid('request_key', 'retry');
                    }

                    return $replayed;
                }
            }
            $existing = ! empty($data['request_key']) ? FinancialGoalTransaction::where('request_key', $data['request_key'])->first() : null;
            if ($recurringId) {
                $existing = FinancialGoalTransaction::where('recurring_transaction_id', $recurringId)->where('date', $data['date'])->first();
            }
            if ($existing) {
                if ((string) $existing->financial_goal_id !== (string) $goal->id || (string) $existing->user_id !== (string) $user->id || (! $recurringId && $existing->request_hash !== $hash)) {
                    self::invalid('request_key', 'retry');
                }

                return $existing;
            }
            $data['request_hash'] = $hash;
            $data['recurring_transaction_id'] = $recurringId;
            if (($goal->status === 'canceled' && $data['type'] !== 'withdrawal')
                || ($goal->status === 'completed' && $data['type'] === 'contribution')
                || ($goal->status !== 'in_progress' && $data['status'] !== 'completed')) {
                self::invalid('financial_goal_id', 'closed');
            }
            $date = Carbon::parse($data['date'])->startOfDay();
            if (($data['status'] === 'completed' && $date->isAfter(Carbon::today())) || ($data['status'] === 'pending' && $date->isBefore(Carbon::today()))) {
                self::invalid('date', 'date');
            }
            if ($data['type'] === 'usage' && $data['status'] !== 'completed') {
                self::invalid('status', 'usage_completed');
            }
            $amount = self::cents($data['amount']);
            if ($amount <= 0) {
                self::invalid('amount', 'amount');
            }
            $reserved = self::cents(self::reserved($account->id, $goal->id, true));
            if ($data['status'] === 'completed') {
                if ($data['type'] !== 'contribution' && $amount > $reserved) {
                    self::invalid('amount', 'reserved');
                }
            }
            if ($data['type'] === 'usage') {
                $transactionId = $request->input('transaction_id');
                if ($transactionId) {
                    $expense = Transaction::whereKey($transactionId)->lockForUpdate()->firstOrFail();
                    if ((string) $expense->account_id !== (string) $account->id || $expense->type !== 'expense' || $expense->status !== 'completed' || Carbon::parse($expense->date)->isAfter(Carbon::today())) {
                        self::invalid('transaction_id', 'expense');
                    }
                    if (FinancialGoalTransaction::where('transaction_id', $expense->id)->where('type', '!=', 'usage')->exists()) {
                        self::invalid('transaction_id', 'expense');
                    }
                    self::permission($account, $user->id, 'viewAccount');
                    self::permission($account, $user->id, 'viewTransaction');
                    // All allocations of a single expense serialize on its account/transaction.
                    $allocated = self::cents(FinancialGoalTransaction::where('transaction_id', $expense->id)->where('type', 'usage')->sum('amount'));
                    if ($amount + $allocated > self::cents($expense->amount)) {
                        self::invalid('amount', 'allocated');
                    }
                    $data['date'] = $expense->date;
                    $data['transaction_id'] = $expense->id;
                } else {
                    if (! $request->input('category_id')) {
                        self::invalid('category_id', 'category');
                    }
                    (new SelectableCategory($user->id, 'expense'))->lockAndValidate($request->input('category_id'));
                    $expenseRequest = Request::create('/', 'POST', array_merge($data, ['type' => 'expense', 'category_id' => $request->input('category_id')]));
                    $expenseRequest->setUserResolver(fn () => $user);
                    $expense = app(TransactionRepository::class)->store($expenseRequest);
                    $data['transaction_id'] = $expense->id;
                    $data['expense_created'] = true;
                }
            }
            $entry = FinancialGoalTransaction::create($data);
            if (! empty($data['request_key'])) {
                DB::table('financial_goal_request_receipts')->insert([
                    'request_key' => $data['request_key'], 'request_hash' => $hash,
                    'financial_goal_id' => $goal->id, 'user_id' => $user->id, 'entry_id' => $entry->id,
                ]);
            }
            $this->sync($goal);
            app(ActivityLogRepository::class)->storeActivity($goal->id, $user->id, 'financial_goal', [
                'type' => $entry->status === 'pending' ? 'financial_transaction_scheduled' : 'financial_transaction_added',
                'transactionType' => $entry->type, 'date' => $entry->date, 'amount' => $entry->amount, 'goalId' => $goal->id,
            ]);

            return $entry;
        }, 3);
    }

    public function change(Request $request, string $id, string $action): FinancialGoalTransaction
    {
        return DB::transaction(function () use ($request, $id, $action) {
            $user = $request->user();
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $original = FinancialGoalTransaction::findOrFail($id);
            $account = Account::whereKey($original->account_id)->lockForUpdate()->firstOrFail();
            $goal = FinancialGoal::whereKey($original->financial_goal_id)->lockForUpdate()->firstOrFail();
            $entry = FinancialGoalTransaction::whereKey($id)->lockForUpdate()->firstOrFail();
            $permission = ['confirm' => 'confirmScheduledFinancialGoalTransactions', 'update' => 'updateFinancialGoalTransaction', 'destroy' => 'destroyFinancialGoalTransaction'][$action];
            self::permission($goal, $user->id, $permission);
            self::permission($account, $user->id, 'createTransaction');
            if ($action === 'update') {
                // Completed entries are immutable: corrections are explicit release/reserve or unlink operations.
                if ($entry->status === 'completed') {
                    self::invalid('amount', 'immutable');
                }
                if ($goal->status !== 'in_progress' || Carbon::parse($request->input('date'))->isBefore(Carbon::today())) {
                    self::invalid('date', 'date');
                }
                $entry->update($request->only(['amount', 'date', 'description']));
            } elseif ($action === 'confirm') {
                if ($entry->status === 'completed') {
                    return $entry; // A retried confirmation does not apply the movement twice.
                }
                if (Carbon::parse($entry->date)->isAfter(Carbon::today())) {
                    self::invalid('date', 'date');
                }
                if ($goal->status !== 'in_progress' || (! $account->active && $entry->type !== 'withdrawal') || (string) $account->currency_id !== (string) $goal->currency_id) {
                    self::invalid('financial_goal_id', 'closed');
                }
                if ($entry->type !== 'contribution' && self::cents($entry->amount) > self::cents(self::reserved($account->id, $goal->id, true))) {
                    self::invalid('amount', 'reserved');
                }
                $entry->update(['status' => 'completed']);
            } else {
                if ($entry->status === 'completed' && $entry->type !== 'usage') {
                    self::invalid('amount', 'immutable');
                }
                // Unlinking preserves the real expense. A shortfall is shown if money is no longer available.
                $entry->delete();
            }
            $this->sync($goal);

            return $entry;
        }, 3);
    }
}
