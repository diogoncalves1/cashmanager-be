<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(0);
            $table->unsignedBigInteger('recurring_transaction_id')->nullable();

            $table->foreign('recurring_transaction_id')->references('id')->on('recurring_transactions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['recurring_transaction_id']);
            $table->dropColumn('is_recurring');
            $table->dropColumn('recurring_transaction_id');
        });
    }
};
