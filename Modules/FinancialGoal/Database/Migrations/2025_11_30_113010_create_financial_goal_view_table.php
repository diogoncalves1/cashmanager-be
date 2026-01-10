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
            CREATE VIEW financial_goal_view AS
            SELECT
                fg.name AS name,
                fg.status AS status,
                fg.description AS description,
                fg.priority AS priority,
                fg.canceled_at AS canceledAt,
                fg.total_amount AS totalAmount,
                c.symbol AS currencySymbol,
                c.code AS currencyCode,
                c.id AS currencyId,
                fg.start_date AS startDate,
                fg.due_date AS dueDate,
                fg.completed_at AS completedAt,
                fg.contributed_amount AS contributedAmount,
                fg.id AS id,
                GROUP_CONCAT(u.name SEPARATOR ', ') AS userNames,
                GROUP_CONCAT(u.id SEPARATOR ', ') AS userIds
                FROM financial_goals AS fg
                JOIN currencies AS c ON c.id = fg.currency_id
                JOIN financial_goal_users AS fgu ON fgu.financial_goal_id = fg.id
                JOIN users AS u ON u.id = fgu.user_id
                GROUP BY fg.id, fg.name, c.symbol;
       ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW financial_goal_view;');
    }
};
