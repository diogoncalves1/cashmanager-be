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
                a.name AS name,
                c.symbol AS currencySymbol,
                c.code AS currencyCode,
                a.type AS type,
                a.id AS id,
                a.balance AS balance,
                a.active AS status,
                GROUP_CONCAT(u.name SEPARATOR ', ') AS userNames,
                GROUP_CONCAT(u.id SEPARATOR ', ') AS user_ids
                FROM accounts AS a
                JOIN currencies AS c ON c.id = a.currency_id
                JOIN account_users AS au ON au.account_id = a.id
                JOIN users AS u ON u.id = au.user_id
                GROUP BY a.id, a.name, c.symbol;
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
