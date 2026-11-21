<?php

namespace Modules\Language\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Language\Repositories\LanguageRepository;
use Tests\Support\ListingTestCase;

class LanguageCacheTest extends ListingTestCase
{
    public function test_language_updates_invalidate_the_cached_code_catalogue(): void
    {
        Schema::table('languages', function (Blueprint $t) {
            $t->string('name')->nullable();
            $t->timestamps();
        });
        $repository = app(LanguageRepository::class);
        $this->assertSame(['en'], $repository->allCodes());
        $repository->update(Request::create('/', 'PUT', ['code' => 'pt']), '1');
        $this->assertSame(['pt'], $repository->allCodes());
    }
}
