<?php

namespace Modules\SharedRoles\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use Tests\Support\ListingTestCase;

class SharedRolePaginationTest extends ListingTestCase
{
    public function test_role_lists_require_authorization_and_return_structured_json(): void
    {
        Gate::define('authorization', fn () => false);
        $this->getJson('/api/v1/shared-roles/paginated')->assertForbidden();
        Gate::define('authorization', fn () => true);
        $this->getJson('/api/v1/shared-roles/paginated?search=reader')->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('data.0.code', 'reader')->assertJsonMissingPath('data.0.action');
    }
}
