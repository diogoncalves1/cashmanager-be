<?php

use Illuminate\Database\Migrations\Migration;
use Modules\FinancialGoal\Services\BackfillGoalRequestReceipts;

return new class extends Migration
{
    public function up(): void
    {
        app(BackfillGoalRequestReceipts::class)->run();
    }

    public function down(): void
    {
        // Durable receipts protect retries even after movements are deleted.
        // Keep them until the original reservation schema is deliberately removed.
    }
};
