<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\FinancialGoal\Services\ConvertGoalReservations;

return new class extends Migration
{
    public function up(): void
    {
        app(ConvertGoalReservations::class)->migrateLegacyGoals();
    }

    public function down(): void
    {
        if (DB::table('financial_goal_conversions')->where('user_id', 0)->exists()) {
            throw new RuntimeException('Restore or reconcile the audited goal conversion before rolling back. Account balances and later movements must not be overwritten automatically.');
        }
    }
};
