<?php

namespace Modules\User\Searches;

use App\Searches\ModuleSearch;

class SearchUserSearch extends ModuleSearch
{
    protected array $fields = ['users.name', 'users.username', 'users.id'];

    protected array $relations = [];
}
