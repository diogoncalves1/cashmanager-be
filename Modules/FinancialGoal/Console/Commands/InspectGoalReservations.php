<?php

namespace Modules\FinancialGoal\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\FinancialGoal\Services\GoalReservations;

class InspectGoalReservations extends Command
{
    protected $signature = 'goals:inspect-reservations {goal : Financial goal ID}';

    protected $description = 'Read historical goal totals and account origins without changing any data';

    public function handle(): int
    {
        $goalId = (string) $this->argument('goal');
        if (! ctype_digit($goalId) || (int) $goalId < 1) {
            $this->error('Enter a positive financial goal ID.');

            return self::FAILURE;
        }
        $goal = DB::table('financial_goals')->where('id', $goalId)->first(['id', 'funding_mode', 'contributed_amount']);
        if (! $goal) {
            $this->error('Financial goal not found.');

            return self::FAILURE;
        }
        if ($goal->funding_mode !== 'legacy') {
            $this->info('This goal already uses reservations; historical conversion is not needed.');

            return self::SUCCESS;
        }
        $entries = DB::table('financial_goal_transactions as entry')
            ->leftJoin('transactions as transaction', 'transaction.id', '=', 'entry.transaction_id')
            ->leftJoin('accounts as account', 'account.id', '=', 'transaction.account_id')
            ->where('entry.financial_goal_id', $goalId)->orderBy('entry.id')
            ->get(['entry.id', 'entry.type', 'entry.status', 'entry.amount', 'account.id as account_id']);
        $accounts = [];
        $missing = [];
        foreach ($entries as $entry) {
            $account = $entry->account_id ?? 'unresolved';
            $accounts[$account] ??= ['contribution' => 0, 'withdrawal' => 0, 'pending' => 0];
            if ($entry->account_id === null) {
                $missing[] = $entry->id;
            }
            if ($entry->status === 'pending') {
                $accounts[$account]['pending']++;
            } elseif ($entry->status === 'completed' && in_array($entry->type, ['contribution', 'withdrawal'], true)) {
                $accounts[$account][$entry->type] += GoalReservations::cents($entry->amount);
            }
        }
        $money = fn (int $cents) => number_format($cents / 100, 2, '.', '');
        $rows = [];
        $calculated = 0;
        $negative = false;
        foreach ($accounts as $id => $amounts) {
            $net = $amounts['contribution'] - $amounts['withdrawal'];
            $calculated += $net;
            $negative = $negative || $net < 0;
            $rows[] = [$id, $money($amounts['contribution']), $money($amounts['withdrawal']), $money($net), $amounts['pending']];
        }
        $stored = GoalReservations::cents($goal->contributed_amount);
        $this->line('Goal: '.$goal->id.' (legacy)');
        $this->line('Stored funding: '.$money($stored));
        $this->line('Completed movement net: '.$money($calculated));
        $this->line('Difference (movements - stored): '.$money($calculated - $stored));
        $this->table(['Account ID', 'Contributions', 'Withdrawals', 'Net reserved', 'Pending entries'], $rows);
        if ($missing) {
            $this->warn('Movements without a historical account: '.implode(', ', $missing));
        }
        if ($negative) {
            $this->warn('At least one account has more completed withdrawals than contributions for this goal.');
        }
        $this->comment('Read-only report. Amounts use the goal currency. No data was changed; matching totals do not validate every conversion requirement.');

        return $calculated === $stored && ! $negative && ! $missing ? self::SUCCESS : self::FAILURE;
    }
}
