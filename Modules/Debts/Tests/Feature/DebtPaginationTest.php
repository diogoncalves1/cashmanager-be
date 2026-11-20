<?php

namespace Modules\Debts\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Debts\Paginates\DebtPaginate;
use Modules\Debts\Repositories\DebtRepository;
use Tests\Support\ListingTestCase;

class DebtPaginationTest extends ListingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('debts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
        });
        Schema::create('debt_users', function (Blueprint $t) {
            $t->unsignedBigInteger('debt_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('shared_role_id');
        });
        Schema::create('debts_view', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('description')->default('');
            $t->string('status')->default('pending');
            $t->decimal('totalAmount')->default(100);
            $t->decimal('paidAmount')->default(0);
            $t->decimal('interestRate')->default(0);
            $t->integer('months')->default(1);
            $t->decimal('monthlyAmount')->default(10);
            $t->string('currencyCode')->default('EUR');
            $t->string('currencySymbol')->default('€');
        });
        for ($id = 1; $id <= 3; $id++) {
            DB::table('debts')->insert(['id' => $id, 'name' => 'Debt '.$id]);
            DB::table('debts_view')->insert(['id' => $id, 'name' => 'Debt '.$id]);
            DB::table('debt_users')->insert(['debt_id' => $id, 'user_id' => $id === 2 ? 11 : 1, 'shared_role_id' => $id === 3 ? 2 : 1]);
        }
    }

    public function test_debt_list_uses_the_unified_contract_and_excludes_private_debts(): void
    {
        $this->mock(DebtRepository::class)->shouldReceive('getStats')->andReturn([]);
        $this->getJson('/api/v1/debts?status[eq]=pending&search=Debt')->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.id', 1)->assertJsonPath('data.0.actions.view', true);
    }

    public function test_remaining_amount_sort_includes_interest(): void
    {
        DB::table('debt_users')->where('debt_id', 3)->update(['shared_role_id' => 1]);
        DB::table('debts_view')->where('id', 1)->update(['interestRate' => 10, 'months' => 12]);
        $r = $this->listingRequest(['order_by' => 'remaining_amount', 'sort_by' => 'desc']);
        $this->assertSame([1, 3], app(DebtPaginate::class)->paginateByUser($r->user(), $r)->pluck('id')->all());
    }
}
