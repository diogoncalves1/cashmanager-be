<?php

namespace Modules\FinancialGoal\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateGoalReservations
{
    public function run(bool $apply = false, ?string $goalId = null): array
    {
        $ids = DB::table('financial_goals')->when($goalId !== null, fn ($q) => $q->where('id', $goalId))->orderBy('id')->pluck('id');
        if ($goalId !== null && $ids->isEmpty()) {
            throw new \InvalidArgumentException('Financial goal not found: '.$goalId);
        }
        $reports = [];
        foreach ($ids as $id) {
            $reports[] = DB::transaction(fn () => $this->goal((string) $id, $apply), 3);
        }

        return $reports;
    }

    private function goal(string $id, bool $apply): array
    {
        // Writes are serialized in the same account-before-goal order as normal reservations.
        $accountIds = DB::table('transactions')->whereIn('id', DB::table('financial_goal_transactions')
            ->where('financial_goal_id', $id)->select('transaction_id'))->pluck('account_id')->unique()->sort()->values();
        $accountQuery = DB::table('accounts')->whereIn('id', $accountIds)->orderBy('id');
        $accounts = ($apply ? $accountQuery->lockForUpdate() : $accountQuery)->get()->keyBy('id');
        $goalQuery = DB::table('financial_goals')->where('id', $id);
        $goal = ($apply ? $goalQuery->lockForUpdate() : $goalQuery)->first();
        $entryQuery = DB::table('financial_goal_transactions')->where('financial_goal_id', $id)->orderBy('id');
        $entries = ($apply ? $entryQuery->lockForUpdate() : $entryQuery)->get();
        $report = ['goalId' => (string) $id, 'status' => 'ready', 'storedAmount' => (float) $goal->contributed_amount,
            'fundedAmount' => 0.0, 'difference' => 0.0, 'accounts' => [], 'issues' => [], 'correctedMovements' => []];

        if (($goal->funding_mode ?? 'legacy') === 'reservation') {
            $funded = $entries->where('status', 'completed')->sum(fn ($e) => GoalReservations::cents($e->amount) * match ($e->type) {
                'contribution' => 1, 'withdrawal' => -1, default => 0,
            }) / 100;
            $report['status'] = 'already_reservation';
            $report['fundedAmount'] = $funded;
            $report['difference'] = (GoalReservations::cents($funded) - GoalReservations::cents($goal->contributed_amount)) / 100;
            // Previous successful deployments already restored balances. Never restore them again.
            if ($apply && $report['difference'] != 0) {
                $this->record($id, $report, ['goal' => $goal, 'entries' => $entries]);
                DB::table('financial_goals')->where('id', $id)->update(['contributed_amount' => $funded]);
            }

            return $report;
        }

        $txQuery = DB::table('transactions')->whereIn('id', $entries->pluck('transaction_id')->filter())->orderBy('id');
        $transactions = ($apply ? $txQuery->lockForUpdate() : $txQuery)->get()->keyBy('id');
        $categories = DB::table('categories')->whereIn('id', $transactions->pluck('category_id')->filter())->get()->keyBy('id');
        $net = [];
        $adjustments = [];
        $updates = [];
        $occurrences = [];
        $markers = Schema::hasTable('financial_goal_neutralized_transactions')
            ? DB::table('financial_goal_neutralized_transactions')->whereIn('transaction_id', $transactions->keys())->get()->keyBy('transaction_id') : collect();
        $issue = function (string $code, $entryId = null) use (&$report): void {
            $report['issues'][] = ['code' => $code, 'movementId' => $entryId === null ? null : (string) $entryId];
        };
        if ($entries->isEmpty() && GoalReservations::cents($goal->contributed_amount) !== 0) {
            $issue('funded_goal_without_history');
        }
        foreach ($entries as $entry) {
            $tx = $transactions->get($entry->transaction_id);
            if (! $tx) {
                $issue('missing_transaction', $entry->id);
                continue;
            }
            $account = $accounts->get($tx->account_id);
            $expectedType = match ($entry->type) {'contribution' => 'expense', 'withdrawal' => 'revenue', default => null};
            $expectedCode = $entry->type === 'contribution' ? 'financialGoalContribution' : 'financialGoalWithdrawal';
            if ($expectedType === null || $tx->type !== $expectedType || $categories->get($tx->category_id)?->code !== $expectedCode) {
                $issue('not_an_internal_goal_transaction', $entry->id);
                continue;
            }
            if (! $account || $account->currency_id === null || $goal->currency_id === null || (string) $account->currency_id !== (string) $goal->currency_id) {
                $issue('missing_account_or_currency_mismatch', $entry->id);
                continue;
            }
            if (DB::table('financial_goal_transactions')->where('transaction_id', $tx->id)->count() !== 1
                || (Schema::hasColumn('debt_payments', 'transaction_id') && DB::table('debt_payments')->where('transaction_id', $tx->id)->exists())) {
                $issue('transaction_linked_more_than_once', $entry->id);
                continue;
            }
            if (! in_array($tx->status, ['completed', 'pending'], true) || GoalReservations::cents($tx->amount) <= 0) {
                $issue('invalid_transaction_amount_or_status', $entry->id);
                continue;
            }
            $recurringId = $tx->recurring_transaction_id ?? null;
            $date = substr($tx->date, 0, 10);
            if ($recurringId !== null) {
                $key = $recurringId.':'.$date;
                $duplicate = isset($occurrences[$key]);
                if (Schema::hasColumn('financial_goal_transactions', 'recurring_transaction_id')) {
                    $duplicate = $duplicate || DB::table('financial_goal_transactions')->where('recurring_transaction_id', $recurringId)
                        ->where('date', $date)->where('id', '!=', $entry->id)->exists();
                }
                if ($duplicate) {
                    $issue('duplicate_recurring_occurrence', $entry->id);
                    continue;
                }
                $occurrences[$key] = true;
            }
            $net[$account->id] ??= 0;
            $adjustments[$account->id] ??= 0;
            $delta = $tx->status === 'completed' ? GoalReservations::cents($tx->amount) * ($tx->type === 'expense' ? 1 : -1) : 0;
            $net[$account->id] += $delta;
            if (! $markers->has($tx->id)) {
                $adjustments[$account->id] += $delta;
            } elseif ((string) $markers[$tx->id]->financial_goal_id !== $id) {
                $issue('neutralized_by_another_goal', $entry->id);
            }
            $update = ['account_id' => $account->id, 'amount' => $tx->amount, 'status' => $tx->status,
                'date' => $date, 'recurring_transaction_id' => $recurringId];
            if (GoalReservations::cents($entry->amount) !== GoalReservations::cents($tx->amount) || $entry->status !== $tx->status || substr($entry->date, 0, 10) !== $date
                || (isset($entry->account_id) && (string) $entry->account_id !== (string) $account->id)) {
                $report['correctedMovements'][] = ['movementId' => (string) $entry->id,
                    'oldAmount' => (float) $entry->amount, 'newAmount' => (float) $tx->amount,
                    'oldStatus' => $entry->status, 'newStatus' => $tx->status,
                    'oldDate' => substr($entry->date, 0, 10), 'newDate' => $date,
                    'oldAccountId' => isset($entry->account_id) ? (string) $entry->account_id : null, 'newAccountId' => (string) $account->id];
            }
            $updates[$entry->id] = $update;
        }
        foreach ($net as $accountId => $amount) {
            if ($amount < 0) {
                $issue('negative_historical_net_for_account:'.$accountId);
            }
            $report['accounts'][] = ['id' => (string) $accountId, 'name' => $accounts[$accountId]->name,
                'reservedAmount' => $amount / 100, 'balanceAdjustment' => $adjustments[$accountId] / 100,
                'balanceBefore' => (float) $accounts[$accountId]->balance,
                'balanceAfter' => (GoalReservations::cents($accounts[$accountId]->balance) + $adjustments[$accountId]) / 100];
        }
        $report['historyComplete'] = count($updates) === $entries->count() && ! ($entries->isEmpty() && GoalReservations::cents($goal->contributed_amount) !== 0);
        $report['fundedAmount'] = array_sum($net) / 100;
        $report['difference'] = (array_sum($net) - GoalReservations::cents($goal->contributed_amount)) / 100;
        if (! $report['historyComplete']) {
            $report['fundedAmount'] = null;
            $report['difference'] = null;
        }
        $report['status'] = $report['issues'] ? 'needs_review' : 'ready';
        if (! $apply) {
            return $report;
        }
        $snapshot = ['goal' => $goal, 'entries' => $entries->values(), 'transactions' => $transactions->values(), 'accounts' => $accounts->values(),
            'balancePolicy' => 'restore_actual_internal_transactions_once', 'amountPolicy' => 'linked_account_transaction'];
        if ($report['issues']) {
            $this->record($id, $report, $snapshot);

            return $report; // Keep this goal usable in its existing accounting model.
        }
        foreach ($entries as $entry) {
            DB::table('financial_goal_transactions')->where('id', $entry->id)->update($updates[$entry->id]);
            $tx = $transactions[$entry->transaction_id];
            if (! $markers->has($tx->id)) {
                DB::table('financial_goal_neutralized_transactions')->insert([
                    'transaction_id' => $tx->id, 'financial_goal_id' => $id,
                    'balance_adjustment' => $tx->status === 'completed' ? (GoalReservations::cents($tx->amount) * ($tx->type === 'expense' ? 1 : -1)) / 100 : 0,
                    'created_at' => now(),
                ]);
            }
        }
        foreach ($adjustments as $accountId => $delta) {
            DB::table('accounts')->where('id', $accountId)->update(['balance' => (GoalReservations::cents($accounts[$accountId]->balance) + $delta) / 100]);
        }
        DB::table('financial_goals')->where('id', $id)->update([
            'funding_mode' => 'reservation', 'contributed_amount' => $report['fundedAmount'],
            'completed_funding' => $goal->status === 'completed' ? $report['fundedAmount'] : null,
        ]);
        $report['status'] = 'converted';
        $this->record($id, $report, $snapshot);

        return $report;
    }

    private function record(string $id, array $report, array $snapshot): void
    {
        $previous = DB::table('financial_goal_reservation_rollouts')->where('financial_goal_id', $id)->first();
        DB::table('financial_goal_reservation_rollouts')->updateOrInsert(['financial_goal_id' => $id], [
            'status' => $report['status'], 'report' => json_encode($report, JSON_THROW_ON_ERROR),
            'snapshot' => $previous && in_array($previous->status, ['converted', 'already_reservation'], true) ? $previous->snapshot : json_encode($snapshot, JSON_THROW_ON_ERROR),
            'created_at' => $previous->created_at ?? now(), 'updated_at' => now(),
        ]);
    }
}
