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
            CREATE VIEW financial_goal_basic_view AS
            SELECT
                fg.id,
                fg.name,
                GROUP_CONCAT(DISTINCT u.id SEPARATOR ', ') AS user_ids,
                fg.status,
                fg.total_amount AS target,
                fg.contributed_amount AS current_amount,
                c.symbol AS currencySymbol,
                c.code AS currencyCode
            FROM financial_goals fg
            JOIN financial_goal_users fgu ON fgu.financial_goal_id = fg.id
            JOIN currencies AS c ON c.id = fg.currency_id
            JOIN users u ON u.id = fgu.user_id
            GROUP BY fg.id;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS financial_goal_basic_view");
    }
};
