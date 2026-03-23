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
            CREATE VIEW debt_payments_view AS
            SELECT
                dp.id AS id,
                d.name AS debtName,
                d.id AS debtId,

                u.name AS userName,
                u.id AS userId,

                a.name AS accountName,
                a.id AS accountId,

                c.symbol AS currencySymbol,
                c.code AS currencyCode,
                c.id AS currencyId,

                dp.amount AS amount,
                dp.date AS date,
                dp.status AS status,
                dp.description AS description,
                dp.interest_rate AS interestRate,
                dp.interest_paid AS interestPaid,
                dp.is_monthly_payment AS isMonthlyPayment

                FROM debt_payments AS dp
                JOIN debts AS d ON d.id = dp.debt_id
                JOIN currencies AS c ON c.id = d.currency_id
                JOIN debt_users AS du ON du.debt_id = d.id
                JOIN users AS u ON u.id = dp.user_id
                LEFT JOIN transactions t ON t.id = dp.transaction_id
                LEFT JOIN accounts a ON a.id = t.account_id
                GROUP BY dp.id, d.name, c.symbol;
            ");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS debt_payments_view");
    }
};
