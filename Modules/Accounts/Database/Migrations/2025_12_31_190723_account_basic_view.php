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
            CREATE VIEW account_basic_view AS
            SELECT
                a.id,
                a.name,
                GROUP_CONCAT(DISTINCT u.id SEPARATOR ', ') AS user_ids,
                a.active
            FROM accounts a
            JOIN account_users au ON au.account_id = a.id
            JOIN users u ON u.id = au.user_id
            GROUP BY a.id;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS account_basic_view");
    }
};
