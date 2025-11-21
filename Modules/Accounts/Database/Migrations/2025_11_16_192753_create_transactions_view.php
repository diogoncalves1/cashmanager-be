<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {DB::statement("
            CREATE VIEW transactions_view AS
            SELECT
                t.id AS id,
                t.date AS date,
                t.status AS status,
                t.amount AS amount,
                t.description AS description,
                t.type AS type,
                t.user_id AS userId,
                ca.name AS categoryName,
                ca.id AS categoryId,
                u.name AS userName,
                a.id AS accountId,
                a.name AS accountName,
                c.symbol AS currencySymbol,
                c.code AS currencyCode
                FROM transactions AS t
                JOIN accounts AS a ON a.id = t.account_id
                JOIN categories AS ca ON ca.id = t.category_id
                JOIN currencies AS c ON c.id = a.currency_id
                JOIN users AS u ON u.id = t.user_id
                GROUP BY t.id, t.date, t.status, t.amount, t.description, t.type, ca.name, u.name, a.id, a.name, c.symbol, c.code;
        ");}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS transactions_view");
    }
};
