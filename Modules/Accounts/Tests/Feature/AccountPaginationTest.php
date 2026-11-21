<?php

namespace Modules\Accounts\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Modules\Accounts\Paginates\AccountPaginate;
use Modules\Accounts\Repositories\AccountRepository;
use Tests\Support\ListingTestCase;

class AccountPaginationTest extends ListingTestCase
{
    public function test_account_endpoint_filters_inactive_accounts_and_preserves_currency_and_totals(): void
    {
        $this->mock(AccountRepository::class)->shouldReceive('getStats')->once()->andReturn(['activeAccounts' => 0]);
        DB::table('accounts')->where('id', 1)->update(['active' => false]);
        $this->getJson('/api/v1/accounts?active[eq]=0')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1)
            ->assertJsonPath('data.0.active', false)->assertJsonPath('data.0.currency.code', 'EUR')
            ->assertJsonPath('data.0.totalTransactions', 0)->assertJsonPath('meta.stats.activeAccounts', 0);
    }

    public function test_membership_and_permission_must_belong_to_the_same_user(): void
    {
        DB::table('account_users')->insert(['account_id' => 3, 'user_id' => 2, 'shared_role_id' => 1]);
        $request = $this->listingRequest();
        $page = app(AccountPaginate::class)->paginateByUser($request->user(), $request);
        $this->assertSame([1], $page->pluck('id')->all());
        $this->assertSame([1], app(AccountRepository::class)->getAccountsByUserPermission($request->user(), 'viewAccount')->pluck('id')->all());
    }
}
