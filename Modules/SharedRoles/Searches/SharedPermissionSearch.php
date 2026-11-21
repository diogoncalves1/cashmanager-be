<?php

namespace Modules\SharedRoles\Searches;

use App\Searches\ModuleSearch;

class SharedPermissionSearch extends ModuleSearch
{
    protected array $fields = ['shared_permissions.code'];

    protected array $relations = [];
}
