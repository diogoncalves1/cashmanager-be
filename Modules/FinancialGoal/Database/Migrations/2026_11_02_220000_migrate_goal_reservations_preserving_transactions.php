<?php

use Illuminate\Database\Migrations\Migration;
use Modules\FinancialGoal\Services\MigrateGoalReservations;

return new class extends Migration
{
    public function up(): void
    {
        app(MigrateGoalReservations::class)->run(true);
    }

    public function down(): void
    {
        throw new RuntimeException('Balance adjustments and reservations require audited reconciliation; they cannot be blindly reversed.');
    }
};
