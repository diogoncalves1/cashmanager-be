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
                fg.created_at AS createdAt,
                fg.updated_at AS updatedAt,

                COALESCE(t.totalTransactions, 0) AS totalTransactions,
                COALESCE(t.totalContributions, 0) AS totalContributions,
                COALESCE(t.totalWithdrawals, 0) AS totalWithdrawals,


                GROUP_CONCAT(u.name SEPARATOR ', ') AS userNames,
                GROUP_CONCAT(u.id SEPARATOR ', ') AS userIds
                FROM financial_goals AS fg
                JOIN currencies AS c ON c.id = fg.currency_id
                JOIN financial_goal_users AS fgu ON fgu.financial_goal_id = fg.id
                JOIN users AS u ON u.id = fgu.user_id
                LEFT JOIN (
                    SELECT
                        financial_goal_id,
                        COUNT(id) AS totalTransactions,
                        SUM(CASE WHEN type = 'contribution' AND status = 'completed' THEN amount ELSE 0 END) AS totalContributions,
                        SUM(CASE WHEN type = 'withdrawal' AND status = 'completed' THEN amount ELSE 0 END) AS totalWithdrawals
                    FROM financial_goal_transactions
                    GROUP BY financial_goal_id
                ) t ON t.financial_goal_id = fg.id
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
