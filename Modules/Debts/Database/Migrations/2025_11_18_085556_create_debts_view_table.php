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
            CREATE VIEW debts_view AS
            SELECT
                d.id AS id,
                d.name AS name,
                c.symbol AS currencySymbol,
                c.code AS currencyCode,
                c.id AS currencyId,
                d.total_amount AS totalAmount,
                d.paid_amount AS paidAmount,
                d.status AS status,
                d.months AS months,
                d.interest_rate AS interestRate,
                d.start_date AS startDate,
                d.due_date AS dueDate,
                d.paid_at AS paidAt,
                d.months_paid AS monthsPaid,
                d.description AS description,

                COALESCE(t.totalTransactions, 0) AS totalTransactions,
                COALESCE(t.totalPayments, 0) AS totalPayments,
                COALESCE(t.interestPaid, 0) AS interestPaid,

                d.monthly_amount AS monthlyAmount,
                GROUP_CONCAT(u.name SEPARATOR ', ') AS userNames,
                GROUP_CONCAT(u.id SEPARATOR ', ') AS userIds
                FROM debts AS d
                JOIN currencies AS c ON c.id = d.currency_id
                JOIN debt_users AS du ON du.debt_id = d.id
                JOIN users AS u ON u.id = du.user_id
                LEFT JOIN (
                    SELECT
                        debt_id,
                        COUNT(id) AS totalTransactions,
                        SUM(interest_paid) as interestPaid,
                        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) AS totalPayments
                    FROM debt_payments
                    GROUP BY debt_id
                ) t ON t.debt_id = d.id
                GROUP BY d.id, d.name, c.symbol;
                ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS debts_view");
    }
};
