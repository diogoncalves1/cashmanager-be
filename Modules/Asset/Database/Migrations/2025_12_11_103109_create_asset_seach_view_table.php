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
        DB::statement("CREATE VIEW asset_search_view AS
            SELECT
                a.id AS id,
                a.ticker AS ticker,
                JSON_UNQUOTE(JSON_EXTRACT(a.fundamentals_json, '$.shortName')) AS name,
                a.logo AS logo,
                c.symbol AS currencySymbol
                FROM assets AS a
                JOIN currencies AS c ON c.code = a.currency
                GROUP BY a.id, a.name, a.ticker, c.symbol;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS accounts_view");
    }
};
