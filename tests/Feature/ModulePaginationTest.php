<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\Accounts\Paginates\AccountPaginate;
use Modules\ApiResponder\Services\ApiResponse;
use Tests\Support\ListingTestCase;

class ModulePaginationTest extends ListingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Route::get('/api/v1/listing-probe', function (Request $request, AccountPaginate $paginate) {
            return ApiResponse::respondPaginated($paginate->paginateByUser($request->user(), $request), $paginate::RESOURCE);
        });
    }

    public function test_invalid_controls_and_filters_return_422_without_accept_header(): void
    {
        foreach ([['page' => 0], ['per_page' => 0], ['per_page' => 101], ['order_by' => 'name desc; drop table users'], ['sort_by' => 'sideways'], ['search' => ['value' => 'x']], ['search' => str_repeat('x', 201)], ['active' => ['ne' => 0]], ['active' => ['eq' => ['bad']]]] as $query) {
            $this->get('/api/v1/listing-probe?'.http_build_query($query))->assertStatus(422)->assertJsonPath('success', false);
        }
        $this->assertSame(3, DB::table('users')->count());
    }

    public function test_pagination_is_stable_for_equal_sort_values_and_counts_only_visible_matches(): void
    {
        foreach ([4, 5, 6] as $id) {
            DB::table('accounts')->insert(['id' => $id, 'name' => 'Same']);
            DB::table('account_users')->insert(['account_id' => $id, 'user_id' => 1, 'shared_role_id' => 1]);
        }
        $first = $this->getJson('/api/v1/listing-probe?search=Same&order_by=name&per_page=2')->assertOk()
            ->assertJsonPath('pagination.total', 3)->assertJsonPath('pagination.last_page', 2)->assertJsonMissingPath('recordsTotal')->assertJsonMissingPath('recordsFiltered');
        $this->assertSame([4, 5], array_column($first->json('data'), 'id'));
        $second = $this->getJson('/api/v1/listing-probe?search=Same&order_by=name&per_page=2&page=2')->assertOk();
        $this->assertSame([6], array_column($second->json('data'), 'id'));
        $this->getJson('/api/v1/listing-probe?page=20')->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('pagination.total', 4);
    }

    public function test_search_treats_wildcards_and_sql_text_as_literal_text(): void
    {
        DB::table('accounts')->where('id', 1)->update(['name' => 'Save 100%_!']);
        foreach (['100%_!', '%', '_', '!'] as $term) {
            $this->getJson('/api/v1/listing-probe?'.http_build_query(['search' => $term]))->assertOk()->assertJsonPath('pagination.total', 1);
        }
        $this->getJson('/api/v1/listing-probe?'.http_build_query(['search' => "%' OR 1=1 --"]))->assertOk()->assertJsonCount(0,'data');
    }
}
