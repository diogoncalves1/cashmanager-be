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
            CREATE VIEW accounts_view AS
SELECT
    a.id,
    a.name,
    a.type,
    a.balance,
    a.active AS status,
    a.created_at AS createdAt,

    c.id AS currencyId,
    c.code AS currencyCode,
    c.symbol AS currencySymbol,

    COALESCE(t.totalTransactions, 0) AS totalTransactions,
    COALESCE(t.totalRevenues, 0) AS totalRevenues,
    COALESCE(t.totalExpenses, 0) AS totalExpenses,

    GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') AS userNames,
    GROUP_CONCAT(DISTINCT u.id SEPARATOR ', ') AS user_ids

FROM accounts a
JOIN currencies c ON c.id = a.currency_id
JOIN account_users au ON au.account_id = a.id
JOIN users u ON u.id = au.user_id

LEFT JOIN (
    SELECT
        account_id,
        COUNT(id) AS totalTransactions,
        SUM(CASE WHEN type = 'revenue' AND status = 'completed' THEN amount ELSE 0 END) AS totalRevenues,
        SUM(CASE WHEN type = 'expense' AND status = 'completed' THEN amount ELSE 0 END) AS totalExpenses
    FROM transactions
    GROUP BY account_id
) t ON t.account_id = a.id

GROUP BY a.id;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS accounts_view");
    }
};
