<?php

namespace Modules\Currency\Tests\Feature;

use Modules\Currency\Repositories\CurrencyRepository;
use Tests\Support\ListingTestCase;

class CurrencyCacheTest extends ListingTestCase
{
    public function test_cached_currency_lookup_returns_a_model_and_drops_deleted_entries(): void
    {
        $repository = app(CurrencyRepository::class);
        $this->assertSame('EUR', $repository->show('1')->code);
        $repository->destroy('1');
        $this->assertNull($repository->show('1'));
    }
}
