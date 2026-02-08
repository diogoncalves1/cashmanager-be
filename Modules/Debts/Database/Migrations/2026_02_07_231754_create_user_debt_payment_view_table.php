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
            CREATE VIEW user_debt_payment_view AS
                SELECT
                        u.id AS userId,
                        d.name AS debtName,
                        d.id AS debtId,
                        u.name AS userName,
                        c.symbol AS currencySymbol,
                        c.code AS currencyCode,
                        du.shared_role_id,
                        SUM(
                            CASE
                                WHEN dp.status = 'completed'
                                THEN dp.amount - (dp.amount * (dp.interest_rate / 100))
                                ELSE 0
                            END
                        ) AS totalPaid
                    FROM debt_users AS du
                    JOIN users AS u ON u.id = du.user_id
                    JOIN debts AS d ON d.id = du.debt_id
                    JOIN currencies AS c ON c.id = d.currency_id
                    LEFT JOIN debt_payments AS dp
                        ON dp.debt_id = d.id
                        AND dp.user_id = u.id
                    GROUP BY
                        u.id,
                        d.id,
                        d.name,
                        u.name,
                        c.symbol,
                        c.code,
                        du.shared_role_id,
                        c.id;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS user_debt_payment_view");
    }
};
