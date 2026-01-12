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
            CREATE VIEW financial_goal_transaction_view AS
            SELECT
                fgt.id AS id,
                fgt.type AS type,
                fgt.amount AS amount,
                fgt.date AS date,
                fgt.description AS description,
                fgt.status AS status,
                u.name AS userName,
                fgt.user_id AS userId,
                fg.name AS financialGoalName,
                fgt.financial_goal_id AS financialGoalId,
                c.symbol AS currencySymbol,
                c.code AS currencyCode,
                c.id AS currencyId,
                a.name AS accountName
                FROM financial_goal_transactions AS fgt
                JOIN financial_goals AS fg ON fg.id = fgt.financial_goal_id
                JOIN currencies AS c ON c.id = fg.currency_id
                JOIN users AS u ON u.id = fgt.user_id
                LEFT JOIN transactions t ON t.id = fgt.transaction_id
                LEFT JOIN accounts a ON a.id = t.account_id
                GROUP BY
                    fgt.id,
                    fgt.type,
                    fgt.amount,
                    fgt.date,
                    fgt.description,
                    fgt.status,
                    u.name,
                    fgt.user_id,
                    fg.name,
                    fgt.financial_goal_id,
                    c.symbol,
                    c.code,
                    c.id,
                    a.name;
            ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW financial_goal_transaction_view;');
    }
};
