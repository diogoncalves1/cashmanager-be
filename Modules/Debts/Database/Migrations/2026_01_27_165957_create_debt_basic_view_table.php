<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE VIEW debt_basic_view AS
            SELECT
                d.id,
                d.name,
                GROUP_CONCAT(DISTINCT u.id SEPARATOR ', ') AS user_ids,
                d.status,
                d.total_amount AS target,
                d.paid_amount AS current_amount,
                c.symbol AS currencySymbol,
                c.code AS currencyCode,
                d.interest_rate AS interestRate,
                d.monthly_amount AS monthlyAmount
            FROM debts d
            JOIN debt_users du ON du.debt_id = d.id
            JOIN currencies AS c ON c.id = d.currency_id
            JOIN users u ON u.id = du.user_id
            GROUP BY d.id;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS debt_basic_view");
    }
};
