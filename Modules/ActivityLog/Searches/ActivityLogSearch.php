<?php

namespace Modules\ActivityLog\Searches;

use App\Searches\ModuleSearch;

class ActivityLogSearch extends ModuleSearch
{
    protected array $fields = [];

    protected array $relations = ['user' => ['users.name']];
}
