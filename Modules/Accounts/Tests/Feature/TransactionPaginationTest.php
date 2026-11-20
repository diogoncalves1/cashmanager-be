<?php

namespace Modules\Accounts\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Modules\Accounts\Paginates\TransactionPaginate;
use Modules\Accounts\Repositories\TransactionRepository;
use Tests\Support\ListingTestCase;

class TransactionPaginationTest extends ListingTestCase
{
    public function test_date_ranges_keep_both_operators_and_search_never_escapes_membership(): void
    {
        foreach ([[1, 1, '2026-11-01'], [2, 1, '2026-11-15'], [3, 1, '2026-12-01'], [4, 2, '2026-11-15'], [5, 3, '2026-11-15']] as [$id,$account,$date]) {
            DB::table('transactions')->insert(['id' => $id, 'account_id' => $account, 'date' => $date, 'description' => 'Monthly bill']);
        }
        $this->mock(TransactionRepository::class)->shouldReceive('getStats')->andReturn([]);
        $this->getJson('/api/v1/transactions?date[gte]=2026-11-10&date[lte]=2026-11-20&search=bill')
            ->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.id', 2)->assertJsonPath('data.0.currency.code', 'EUR');
        $r = $this->listingRequest(['dateFrom' => '2026-11-10', 'dateTo' => '2026-11-20']);
        $this->assertSame([2], app(TransactionPaginate::class)->paginateByUser($r->user(), $r)->pluck('id')->all());
    }

    public function test_in_filters_accept_arrays_and_bracket_notation(): void
    {
        DB::table('transactions')->insert(['account_id' => 1, 'date' => '2026-11-15']);
        foreach ([['account_id' => ['in' => [1, 2]]], ['account_id' => ['in' => '[1,2]']]] as $input) {
            $r = $this->listingRequest($input);
            $this->assertSame(1, app(TransactionPaginate::class)->paginateByUser($r->user(), $r)->total());
        }
    }
}
