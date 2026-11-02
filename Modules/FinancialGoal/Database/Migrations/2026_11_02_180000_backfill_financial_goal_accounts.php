<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Recover the historical account without changing money, transaction links
        // or funding mode. Unknown origins remain unresolved for explicit review.
        DB::table('financial_goal_transactions')
            ->whereNull('account_id')
            ->whereExists(DB::table('financial_goals')->selectRaw('1')
                ->whereColumn('financial_goals.id', 'financial_goal_transactions.financial_goal_id')
                ->where('funding_mode', 'legacy'))
            ->whereExists(DB::table('transactions as historical_transaction')->selectRaw('1')
                ->join('accounts as historical_account', 'historical_account.id', '=', 'historical_transaction.account_id')
                ->whereColumn('historical_transaction.id', 'financial_goal_transactions.transaction_id'))
            ->update(['account_id' => DB::raw('(SELECT historical_transaction.account_id FROM transactions AS historical_transaction WHERE historical_transaction.id = financial_goal_transactions.transaction_id)')]);
    }

    public function down(): void
    {
        // Keep recovered associations. Clearing them could erase later reviewed data;
        // the preceding schema migration removes the column on a full rollback.
    }
};
