<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS financial_goal_transaction_view');
        Schema::table('financial_goals', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->change();
            $table->decimal('contributed_amount', 15, 2)->default(0)->change();
            // Existing goals retain their accounting until explicitly reconciled.
            $table->string('funding_mode', 20)->default('legacy');
            $table->decimal('completed_funding', 15, 2)->nullable();
        });
        Schema::table('financial_goal_transactions', function (Blueprint $table) {
            $table->string('type', 20)->change();
            $table->decimal('amount', 15, 2)->change();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->string('request_key', 100)->nullable()->unique();
            $table->char('request_hash', 64)->nullable();
            $table->boolean('expense_created')->default(false);
            $table->unsignedBigInteger('recurring_transaction_id')->nullable();
            $table->unique(['recurring_transaction_id', 'date'], 'goal_recurring_occurrence_unique');
        });
        Schema::create('financial_goal_request_receipts', function (Blueprint $table) {
            $table->string('request_key', 100)->primary();
            $table->unsignedBigInteger('financial_goal_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('entry_id');
            $table->char('request_hash', 64);
        });
        Schema::create('financial_goal_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_goal_id')->unique()->constrained('financial_goals')->restrictOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->json('snapshot');
            $table->timestamps();
        });
        DB::statement("CREATE VIEW financial_goal_transaction_view AS
            SELECT fgt.id, fgt.type, fgt.amount, fgt.date, fgt.description, fgt.status,
                fgt.transaction_id AS transactionId, fgt.expense_created AS expenseCreated,
                fg.funding_mode AS fundingMode,
                u.name AS userName, fgt.user_id AS userId, fg.name AS financialGoalName,
                fgt.financial_goal_id AS financialGoalId,
                c.symbol AS currencySymbol, c.code AS currencyCode, c.id AS currencyId,
                a.id AS accountId, a.name AS accountName, a.type AS accountType,
                sr.name AS sharedRoleName,
                COALESCE((SELECT SUM(CASE WHEN p.type = 'contribution' THEN p.amount ELSE -p.amount END)
                    FROM financial_goal_transactions p
                    WHERE p.financial_goal_id = fgt.financial_goal_id AND p.status = 'completed'
                    AND (p.date < fgt.date OR (p.date = fgt.date AND p.id < fgt.id))), 0) AS balanceBefore
            FROM financial_goal_transactions fgt
            JOIN financial_goals fg ON fg.id = fgt.financial_goal_id
            JOIN currencies c ON c.id = fg.currency_id
            JOIN users u ON u.id = fgt.user_id
            LEFT JOIN financial_goal_users fgu ON fgu.user_id = fgt.user_id AND fgu.financial_goal_id = fg.id
            LEFT JOIN shared_roles sr ON sr.id = fgu.shared_role_id
            LEFT JOIN transactions t ON t.id = fgt.transaction_id
            LEFT JOIN accounts a ON a.id = COALESCE(fgt.account_id, t.account_id)");
    }

    public function down(): void
    {
        if (DB::table('financial_goals')->where('funding_mode', 'reservation')->exists()) {
            throw new RuntimeException('Reconcile reservation data from a backup before rolling back this migration.');
        }
        DB::statement('DROP VIEW IF EXISTS financial_goal_transaction_view');
        Schema::dropIfExists('financial_goal_conversions');
        Schema::dropIfExists('financial_goal_request_receipts');
        Schema::table('financial_goal_transactions', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropUnique('goal_recurring_occurrence_unique');
            $table->dropUnique(['request_key']);
            $table->dropColumn(['account_id', 'request_key', 'request_hash', 'expense_created', 'recurring_transaction_id']);
        });
        Schema::table('financial_goals', fn (Blueprint $table) => $table->dropColumn(['funding_mode', 'completed_funding']));
        (require __DIR__.'/2025_11_30_161654_create_financial_goal_transaction_view_table.php')->up();
    }
};
