<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite rebuilds tables for change(); its dependent view must be removed first.
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('DROP VIEW IF EXISTS financial_goal_transaction_view');
        }
        // Each addition is conditional: earlier production attempts may have
        // installed the schema even though their accounting migration failed.
        Schema::table('financial_goals', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->change();
            $table->decimal('contributed_amount', 15, 2)->default(0)->change();
        });
        $this->column('financial_goals', 'completed_funding', fn ($t) => $t->decimal('completed_funding', 15, 2)->nullable());
        Schema::table('financial_goal_transactions', function (Blueprint $table) {
            $table->string('type', 20)->change();
            $table->decimal('amount', 15, 2)->change();
        });
        $this->column('financial_goal_transactions', 'account_id', fn ($t) => $t->foreignId('account_id')->nullable()->constrained('accounts')->restrictOnDelete());
        $this->column('financial_goal_transactions', 'request_key', fn ($t) => $t->string('request_key', 100)->nullable()->unique());
        $this->column('financial_goal_transactions', 'request_hash', fn ($t) => $t->char('request_hash', 64)->nullable());
        $this->column('financial_goal_transactions', 'expense_created', fn ($t) => $t->boolean('expense_created')->default(false));
        $this->column('financial_goal_transactions', 'recurring_transaction_id', function ($t) {
            $t->unsignedBigInteger('recurring_transaction_id')->nullable();
            $t->unique(['recurring_transaction_id', 'date'], 'goal_recurring_occurrence_unique');
        });
        if (! Schema::hasTable('financial_goal_request_receipts')) {
            Schema::create('financial_goal_request_receipts', function (Blueprint $t) {
                $t->string('request_key', 100)->primary();
                $t->unsignedBigInteger('financial_goal_id');
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('entry_id');
                $t->char('request_hash', 64);
            });
        }
        if (! Schema::hasTable('financial_goal_reservation_rollouts')) {
            Schema::create('financial_goal_reservation_rollouts', function (Blueprint $t) {
                $t->unsignedBigInteger('financial_goal_id')->primary();
                $t->string('status', 30);
                $t->json('report');
                $t->json('snapshot')->nullable();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('financial_goal_neutralized_transactions')) {
            Schema::create('financial_goal_neutralized_transactions', function (Blueprint $t) {
                $t->unsignedBigInteger('transaction_id')->primary();
                $t->unsignedBigInteger('financial_goal_id')->index();
                $t->decimal('balance_adjustment', 15, 2);
                $t->timestamp('created_at');
            });
        }
        if (! Schema::hasTable('financial_goal_reservation_rollout_events')) {
            Schema::create('financial_goal_reservation_rollout_events', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('financial_goal_id')->index('goal_rollout_event_goal_idx');
                $t->json('report');
                $t->json('snapshot');
                $t->timestamp('created_at');
            });
        }
        $this->view();
    }

    private function column(string $table, string $name, callable $definition): void
    {
        if (! Schema::hasColumn($table, $name)) {
            Schema::table($table, $definition);
        }
    }

    private function view(): void
    {
        DB::statement('DROP VIEW IF EXISTS financial_goal_transaction_view');
        DB::statement("CREATE VIEW financial_goal_transaction_view AS
            SELECT fgt.id, fgt.type, fgt.amount, fgt.date, fgt.description, fgt.status,
                fgt.transaction_id AS transactionId, fgt.expense_created AS expenseCreated,
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
        // Additive production schema may contain real reservations and receipts.
        throw new RuntimeException('Reservation rollout is forward-only. Reconcile data before reverting application/schema versions.');
    }
};
