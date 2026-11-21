<?php

namespace Modules\Debts\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\ListingTestCase;

class DebtPaymentPaginationTest extends ListingTestCase
{
    public function test_payments_are_scoped_through_the_debt_and_use_debt_permissions(): void
    {
        Schema::create('debts', function (Blueprint $t) {
            $t->id();
        });
        Schema::create('debt_users', function (Blueprint $t) {
            $t->unsignedBigInteger('debt_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('shared_role_id');
        });
        Schema::create('debt_payments_view', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('debtId');
            $t->unsignedBigInteger('userId')->default(2);
            $t->string('debtName');
            $t->string('description')->default('');
            $t->date('date');
            $t->string('status')->default('pending');
            $t->decimal('amount')->default(10);
            $t->decimal('interestPaid')->default(1);
            $t->string('currencyCode')->default('EUR');
            $t->string('currencySymbol')->default('€');
        });
        DB::table('debts')->insert([['id' => 1], ['id' => 2]]);
        DB::table('debt_users')->insert([['debt_id' => 1, 'user_id' => 1, 'shared_role_id' => 1], ['debt_id' => 2, 'user_id' => 11, 'shared_role_id' => 1]]);
        DB::table('debt_payments_view')->insert([['id' => 1, 'debtId' => 1, 'debtName' => 'Loan', 'date' => '2026-01-01'], ['id' => 2, 'debtId' => 2, 'debtName' => 'Loan', 'date' => '2026-01-01']]);
        $this->getJson('/api/v1/debt-payments?debtId=1&date[gte]=2025-01-01&date[lte]=2026-12-31&search=Loan')
            ->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.actions.view', true)->assertJsonPath('data.0.actions.confirm', false);
    }
}
