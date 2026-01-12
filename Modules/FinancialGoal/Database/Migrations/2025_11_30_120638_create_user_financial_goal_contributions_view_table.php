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
            CREATE VIEW user_financial_goal_contributions_view AS
                SELECT
                    u.id AS userId,
                    fg.name AS goalName,
                    fg.id AS goalId,
                    u.name AS userName,
                    c.symbol AS currencySymbol,
                    c.code AS currencyCode,
                    SUM(
                        CASE
                            WHEN fgt.status = 'completed' AND fgt.type = 'contribution'
                            THEN fgt.amount
                            ELSE 0
                        END
                    ) AS totalContributed
                FROM financial_goal_users AS fgu
                JOIN users AS u ON u.id = fgu.user_id
                JOIN financial_goals AS fg ON fg.id = fgu.financial_goal_id
                JOIN currencies AS c ON c.id = fg.currency_id
                LEFT JOIN financial_goal_transactions AS fgt
                    ON fgt.financial_goal_id = fg.id
                    AND fgt.user_id = u.id
                GROUP BY
                    u.id,
                    fg.id,
                    fg.name,
                    u.name,
                    c.symbol,
                    c.code,
                    c.id;
       ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW user_financial_goal_contributions_view;');
    }
};
