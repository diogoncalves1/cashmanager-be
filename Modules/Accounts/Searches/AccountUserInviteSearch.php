<?php

namespace Modules\Accounts\Searches;

use App\Searches\ModuleSearch;

class AccountUserInviteSearch extends ModuleSearch
{
    protected array $fields = [];

    protected array $relations = ['sender' => ['users.name'], 'user' => ['users.name'], 'account' => ['name']];
}
