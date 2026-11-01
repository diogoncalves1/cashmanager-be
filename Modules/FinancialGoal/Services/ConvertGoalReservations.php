<?php

namespace Modules\FinancialGoal\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\Transaction;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\User\Entities\User;

class ConvertGoalReservations
{
    public function preview(Request $request, string $id, bool $apply = false): array
    {
        return DB::transaction(function () use ($request, $id, $apply) {
            $user = $request->user();
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $goal = FinancialGoal::findOrFail($id);
            GoalReservations::permission($goal, $user->id, 'updateFinancialGoal');
            GoalReservations::permission($goal, $user->id, 'storeFinancialGoalTransaction');
            // Same account-before-goal order as normal reservation operations.
            $ids = Transaction::whereIn('id', $goal->transactions()->select('transaction_id'))->orderBy('account_id')->pluck('account_id')->unique();
            $accounts = Account::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $goal = FinancialGoal::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($goal->funding_mode !== 'legacy') {
                GoalReservations::invalid('financial_goal_id', 'converted');
            }
            $entries = $goal->transactions()->orderBy('id')->lockForUpdate()->get();
            $transactions = Transaction::with('category')->whereIn('id', $entries->pluck('transaction_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $deltas = [];
            $occurrences = [];
            foreach ($entries as $entry) {
                $transaction = $transactions->get($entry->transaction_id);
                $expected = $entry->type === 'contribution' ? 'expense' : 'revenue';
                $code = $entry->type === 'contribution' ? 'financialGoalContribution' : 'financialGoalWithdrawal';
                if (! $transaction || ! in_array($entry->type, ['contribution', 'withdrawal'])
                    || $transaction->type !== $expected || $transaction->status !== $entry->status
                    || GoalReservations::cents($transaction->amount) !== GoalReservations::cents($entry->amount)
                    || $transaction->category?->code !== $code
                    || FinancialGoalTransaction::where('transaction_id', $transaction->id)->count() !== 1) {
                    GoalReservations::invalid('financial_goal_id', 'conversion');
                }
                if (Schema::hasColumn('debt_payments', 'transaction_id')
                    && DB::table('debt_payments')->where('transaction_id', $transaction->id)->exists()) {
                    GoalReservations::invalid('financial_goal_id', 'conversion');
                }
                if ($transaction->recurring_transaction_id) {
                    $occurrence = $transaction->recurring_transaction_id.':'.$entry->date;
                    if (isset($occurrences[$occurrence]) || FinancialGoalTransaction::where('recurring_transaction_id', $transaction->recurring_transaction_id)->where('date', $entry->date)->exists()) {
                        GoalReservations::invalid('financial_goal_id', 'conversion');
                    }
                    $occurrences[$occurrence] = true;
                }
                $account = $accounts->get($transaction->account_id);
                if (! $account || (string) $account->currency_id !== (string) $goal->currency_id) {
                    GoalReservations::invalid('account_id', 'conversion');
                }
                GoalReservations::permission($account, $user->id, 'viewAccount');
                GoalReservations::permission($account, $user->id, 'createTransaction');
                GoalReservations::permission($account, $user->id, 'destroyTransaction');
                $deltas[$account->id] ??= 0;
                if ($entry->status === 'completed') {
                    $deltas[$account->id] += GoalReservations::cents($entry->amount) * ($expected === 'expense' ? 1 : -1);
                }
            }
            if (array_sum($deltas) !== GoalReservations::cents($goal->contributed_amount) || count(array_filter($deltas, fn ($amount) => $amount < 0))) {
                GoalReservations::invalid('financial_goal_id', 'conversion');
            }
            $snapshot = ['goal' => $goal->getAttributes(), 'entries' => $entries->map->getAttributes()->all(),
                'transactions' => $transactions->map->getAttributes()->values()->all(),
                'accounts' => $accounts->map->getAttributes()->values()->all()];
            $token = hash('sha256', json_encode($snapshot));
            $preview = ['token' => $token, 'accounts' => $accounts->map(fn ($account) => [
                'id' => (string) $account->id, 'name' => $account->name,
                'balanceBefore' => (float) $account->balance,
                'balanceAfter' => (GoalReservations::cents($account->balance) + ($deltas[$account->id] ?? 0)) / 100,
                'reservedAmount' => ($deltas[$account->id] ?? 0) / 100,
            ])->values(), 'movementCount' => $entries->count()];
            if (! $apply) {
                return $preview;
            }
            if (! $request->boolean('confirm')) {
                GoalReservations::invalid('confirm', 'confirm');
            }
            if (! hash_equals($token, (string) $request->input('token'))) {
                GoalReservations::invalid('token', 'stale');
            }
            DB::table('financial_goal_conversions')->insert(['financial_goal_id' => $goal->id, 'user_id' => $user->id,
                'snapshot' => json_encode($snapshot), 'created_at' => now(), 'updated_at' => now()]);
            foreach ($entries as $entry) {
                $entry->account_id = $transactions[$entry->transaction_id]->account_id;
                $entry->recurring_transaction_id = $transactions[$entry->transaction_id]->recurring_transaction_id;
                $entry->transaction_id = null;
                $entry->save();
            }
            // Keep the entire original transaction in the audit snapshot. Removing the
            // reconciled internal transfer makes every existing account report consistent.
            Transaction::whereIn('id', $transactions->keys())->delete();
            foreach ($accounts as $account) {
                $account->balance = (GoalReservations::cents($account->balance) + ($deltas[$account->id] ?? 0)) / 100;
                $account->save();
            }
            $goal->funding_mode = 'reservation';
            if ($goal->status === 'completed') {
                $goal->completed_funding = $goal->contributed_amount;
            }
            $goal->save();

            return $preview;
        }, 3);
    }
}
