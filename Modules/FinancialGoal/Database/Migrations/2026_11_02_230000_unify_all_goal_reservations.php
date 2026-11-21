<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\FinancialGoal\Services\MigrateGoalReservations;

return new class extends Migration
{
    public function up(): void
    {
        // Also repairs deployments where 210000/220000 already ran and a goal
        // contains both historical transactions and direct reservation entries.
        (require __DIR__.'/2026_11_02_210000_prepare_goal_reservation_rollout.php')->up();
        app(MigrateGoalReservations::class)->run(true);
        if (Schema::hasColumn('financial_goals', 'funding_mode')) {
            Schema::table('financial_goals', fn (Blueprint $table) => $table->dropColumn('funding_mode'));
        }
    }

    public function down(): void
    {
        throw new RuntimeException('The unified goal ledger cannot be split back into historical accounting modes.');
    }
};
