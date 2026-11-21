<?php

namespace Modules\SharedRoles\Searches;

use App\Searches\ModuleSearch;

class SharedRoleSearch extends ModuleSearch
{
    protected array $fields = ['shared_roles.code'];

    protected array $relations = [];
}
