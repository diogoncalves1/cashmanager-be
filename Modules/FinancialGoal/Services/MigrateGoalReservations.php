<?php

namespace Modules\FinancialGoal\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateGoalReservations
{
    public function run(bool $apply = false, ?string $goalId = null): array
    {
        $run = function () use ($apply, $goalId) {
            // A migration converts the complete dataset or changes no financial data.
            // Account-first locking matches ordinary reservation writes.
            if ($apply) {
                DB::table('accounts')->orderBy('id')->lockForUpdate()->get(['id']);
            }
            $query = DB::table('financial_goals')->when($goalId !== null, fn ($q) => $q->where('id', $goalId))->orderBy('id');
            $goals = ($apply ? $query->lockForUpdate() : $query)->get();
            if ($goalId !== null && $goals->isEmpty()) {
                throw new \InvalidArgumentException('Financial goal not found: '.$goalId);
            }
            $plans = $goals->map(fn ($goal) => $this->plan($goal, $apply));
            $reports = $plans->pluck('report')->all();
            if ($apply) {
                $blocked = collect($reports)->where('status', 'blocked');
                if ($blocked->isNotEmpty()) {
                    throw new \RuntimeException('No goals were converted. Resolve the following historical origins: '.json_encode($blocked->values(), JSON_THROW_ON_ERROR));
                }
                foreach ($plans as $plan) {
                    $this->apply($plan);
                }
                foreach ($reports as &$report) {
                    if ($report['status'] === 'ready') {
                        $report['status'] = 'converted';
                    }
                }
            }

            return $reports;
        };

        return $apply ? DB::transaction($run, 3) : $run();
    }

    private function plan(object $goal, bool $lock): array
    {
        $entryQuery = DB::table('financial_goal_transactions')->where('financial_goal_id', $goal->id)->orderBy('id');
        $entries = ($lock ? $entryQuery->lockForUpdate() : $entryQuery)->get();
        $txQuery = DB::table('transactions')->whereIn('id', $entries->pluck('transaction_id')->filter())->orderBy('id');
        $transactions = ($lock ? $txQuery->lockForUpdate() : $txQuery)->get()->keyBy('id');
        $accounts = DB::table('accounts')->get()->keyBy('id');
        $categories = DB::table('categories')->whereIn('id', $transactions->pluck('category_id')->filter())->get()->keyBy('id');
        $markers = Schema::hasTable('financial_goal_neutralized_transactions')
            ? DB::table('financial_goal_neutralized_transactions')->whereIn('transaction_id', $transactions->keys())->get()->keyBy('transaction_id') : collect();
        $audit = Schema::hasTable('financial_goal_conversions')
            ? DB::table('financial_goal_conversions')->where('financial_goal_id', $goal->id)->first() : null;
        $prior = $audit ? json_decode($audit->snapshot, true, 512, JSON_THROW_ON_ERROR) : [];
        $priorEntries = collect($prior['entries'] ?? [])->keyBy('id');
        $priorTransactions = collect($prior['transactions'] ?? [])->keyBy('id');
        $priorRefunded = ($prior['conversion']['balancePolicy'] ?? null) === 'restore_internal_movements';
        $report = ['goalId' => (string) $goal->id, 'status' => 'ready', 'storedAmount' => (float) $goal->contributed_amount,
            'fundedAmount' => 0.0, 'reservedAmount' => 0.0, 'usedAmount' => 0.0, 'difference' => 0.0,
            'accounts' => [], 'issues' => [], 'transactionDifferences' => [], 'assignedMovements' => []];
        $updates = $neutralize = $adjustments = $net = $occurrences = [];
        $funded = $used = 0;
        $issue = function (string $code, $entryId = null) use (&$report): void {
            $report['issues'][] = ['code' => $code, 'movementId' => $entryId === null ? null : (string) $entryId];
        };
        if ($entries->isEmpty() && GoalReservations::cents($goal->contributed_amount) !== 0) {
            $issue('funded_goal_without_history');
        }
        foreach ($entries as $entry) {
            if (! in_array($entry->type, ['contribution', 'withdrawal', 'usage'], true)
                || ! in_array($entry->status, ['pending', 'completed'], true) || GoalReservations::cents($entry->amount) <= 0) {
                $issue('invalid_goal_movement', $entry->id);
                continue;
            }
            $tx = $transactions->get($entry->transaction_id);
            $accountId = $entry->account_id ?? null;
            $recurringId = $entry->recurring_transaction_id ?? null;
            $update = [];
            if ($entry->type === 'usage') {
                // A usage allocates part of a real purchase: its amount is not the full expense amount.
                if (! $tx || $tx->type !== 'expense' || $tx->status !== 'completed' || $entry->status !== 'completed'
                    || ($accountId !== null && (string) $accountId !== (string) $tx->account_id)
                    || DB::table('financial_goal_transactions')->where('transaction_id', $tx->id)->where('type', '!=', 'usage')->exists()
                    || GoalReservations::cents(DB::table('financial_goal_transactions')->where('transaction_id', $tx->id)->where('type', 'usage')->sum('amount')) > GoalReservations::cents($tx->amount)) {
                    $issue('invalid_linked_expense', $entry->id);
                    continue;
                }
                $accountId = $tx->account_id;
            } elseif ($entry->transaction_id !== null) {
                if (! $tx) {
                    $original = $priorEntries->get($entry->id);
                    $archivedTx = $original ? $priorTransactions->get($original['transaction_id'] ?? null) : null;
                    if (! $priorRefunded || ! $archivedTx) {
                        $issue('missing_transaction', $entry->id);
                        continue;
                    }
                    // A previous deployed converter deleted this transaction after restoring its balance.
                    $accountId = $archivedTx['account_id'];
                    $recurringId ??= $archivedTx['recurring_transaction_id'] ?? null;
                    $update['transaction_id'] = null;
                } else {
                    $expected = $entry->type === 'contribution' ? 'expense' : 'revenue';
                    $code = $entry->type === 'contribution' ? 'financialGoalContribution' : 'financialGoalWithdrawal';
                    if ($tx->type !== $expected
                        || ! in_array($tx->status, ['pending', 'completed'], true) || GoalReservations::cents($tx->amount) <= 0) {
                        $issue('not_an_internal_goal_transaction', $entry->id);
                        continue;
                    }
                    if (DB::table('financial_goal_transactions')->where('transaction_id', $tx->id)->count() !== 1
                        || (Schema::hasColumn('debt_payments', 'transaction_id') && DB::table('debt_payments')->where('transaction_id', $tx->id)->exists())) {
                        $issue('transaction_linked_more_than_once', $entry->id);
                        continue;
                    }
                    $accountId = $tx->account_id;
                    $recurringId ??= $tx->recurring_transaction_id ?? null;
                    if (GoalReservations::cents($tx->amount) !== GoalReservations::cents($entry->amount) || $tx->status !== $entry->status
                        || $categories->get($tx->category_id)?->code !== $code) {
                        $report['transactionDifferences'][] = ['movementId' => (string) $entry->id,
                            'goalAmount' => (float) $entry->amount, 'transactionAmount' => (float) $tx->amount,
                            'goalStatus' => $entry->status, 'transactionStatus' => $tx->status,
                            'transactionCategoryCode' => $categories->get($tx->category_id)?->code];
                    }
                    if ($markers->has($tx->id)) {
                        if ((string) $markers[$tx->id]->financial_goal_id !== (string) $goal->id) {
                            $issue('neutralized_by_another_goal', $entry->id);
                        }
                    } else {
                        $alreadyRestored = $priorRefunded && $priorTransactions->has($tx->id);
                        $delta = ! $alreadyRestored && $tx->status === 'completed'
                            ? GoalReservations::cents($tx->amount) * ($tx->type === 'expense' ? 1 : -1) : 0;
                        $neutralize[$tx->id] = $delta;
                        $adjustments[$accountId] = ($adjustments[$accountId] ?? 0) + $delta;
                    }
                }
            } elseif ($accountId === null && $priorRefunded) {
                $original = $priorEntries->get($entry->id);
                $archivedTx = $original ? $priorTransactions->get($original['transaction_id'] ?? null) : null;
                $accountId = $archivedTx['account_id'] ?? $original['account_id'] ?? null;
                $recurringId ??= $archivedTx['recurring_transaction_id'] ?? null;
            }
            $account = $accounts->get($accountId);
            if (! $account || $account->currency_id === null || $goal->currency_id === null || (string) $account->currency_id !== (string) $goal->currency_id) {
                $issue('missing_account_or_currency_mismatch', $entry->id);
                continue;
            }
            if ($recurringId !== null) {
                $key = $recurringId.':'.substr($entry->date, 0, 10);
                if (isset($occurrences[$key]) || (Schema::hasColumn('financial_goal_transactions', 'recurring_transaction_id')
                    && DB::table('financial_goal_transactions')->where('recurring_transaction_id', $recurringId)->where('date', $entry->date)->where('id', '!=', $entry->id)->exists())) {
                    $issue('duplicate_recurring_occurrence', $entry->id);
                }
                $occurrences[$key] = true;
            }
            if ((string) ($entry->account_id ?? '') !== (string) $accountId) {
                $update['account_id'] = $accountId;
                $report['assignedMovements'][] = ['movementId' => (string) $entry->id, 'accountId' => (string) $accountId];
            }
            if ((string) ($entry->recurring_transaction_id ?? '') !== (string) ($recurringId ?? '')) {
                $update['recurring_transaction_id'] = $recurringId;
            }
            if ($update) {
                $updates[$entry->id] = $update;
            }
            $net[$accountId] ??= 0;
            if ($entry->status === 'completed') {
                $cents = GoalReservations::cents($entry->amount);
                $signed = $entry->type === 'contribution' ? $cents : -$cents;
                $net[$accountId] += $signed;
                if ($entry->type === 'usage') {
                    $used += $cents;
                } else {
                    $funded += $signed;
                }
            }
        }
        foreach ($net as $accountId => $amount) {
            if ($amount < 0) {
                $issue('negative_historical_net_for_account:'.$accountId);
            }
            $delta = $adjustments[$accountId] ?? 0;
            $report['accounts'][] = ['id' => (string) $accountId, 'name' => $accounts[$accountId]->name,
                'reservedAmount' => $amount / 100, 'balanceAdjustment' => $delta / 100,
                'balanceBefore' => (float) $accounts[$accountId]->balance,
                'balanceAfter' => (GoalReservations::cents($accounts[$accountId]->balance) + $delta) / 100];
        }
        $report['fundedAmount'] = $funded / 100;
        $report['usedAmount'] = $used / 100;
        $report['reservedAmount'] = ($funded - $used) / 100;
        $report['difference'] = ($funded - GoalReservations::cents($goal->contributed_amount)) / 100;
        $changed = $updates || $neutralize || $report['difference'] != 0
            || ($goal->status === 'completed' && GoalReservations::cents($goal->completed_funding ?? 0) !== $funded);
        $report['status'] = $report['issues'] ? 'blocked' : ($changed ? 'ready' : 'unchanged');
        $snapshot = ['goal' => $goal, 'entries' => $entries->values(), 'transactions' => $transactions->values(),
            'accounts' => $accounts->only(array_keys($net))->values(),
            'amountPolicy' => 'preserve_goal_ledger', 'balancePolicy' => 'reverse_actual_internal_transactions_once'];

        return compact('goal', 'report', 'snapshot', 'updates', 'neutralize', 'adjustments');
    }

    private function apply(array $plan): void
    {
        $goal = $plan['goal'];
        $report = $plan['report'];
        $snapshot = $plan['snapshot'];
        $updates = $plan['updates'];
        $neutralize = $plan['neutralize'];
        $adjustments = $plan['adjustments'];
        if ($report['status'] === 'unchanged') {
            return;
        }
        foreach ($updates as $entryId => $values) {
            DB::table('financial_goal_transactions')->where('id', $entryId)->update($values);
        }
        foreach ($neutralize as $transactionId => $delta) {
            DB::table('financial_goal_neutralized_transactions')->insert(['transaction_id' => $transactionId,
                'financial_goal_id' => $goal->id, 'balance_adjustment' => $delta / 100, 'created_at' => now()]);
        }
        foreach ($adjustments as $accountId => $delta) {
            DB::table('accounts')->where('id', $accountId)->increment('balance', $delta / 100);
        }
        DB::table('financial_goals')->where('id', $goal->id)->update(['contributed_amount' => $report['fundedAmount'],
            'completed_funding' => $goal->status === 'completed' ? $report['fundedAmount'] : null]);
        $report['status'] = 'converted';
        $previous = DB::table('financial_goal_reservation_rollouts')->where('financial_goal_id', $goal->id)->first();
        DB::table('financial_goal_reservation_rollouts')->updateOrInsert(['financial_goal_id' => $goal->id], [
            'status' => 'converted', 'report' => json_encode($report, JSON_THROW_ON_ERROR),
            'snapshot' => $previous->snapshot ?? json_encode($snapshot, JSON_THROW_ON_ERROR),
            'created_at' => $previous->created_at ?? now(), 'updated_at' => now(),
        ]);
        DB::table('financial_goal_reservation_rollout_events')->insert(['financial_goal_id' => $goal->id,
            'report' => json_encode($report, JSON_THROW_ON_ERROR), 'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR), 'created_at' => now()]);
    }
}
