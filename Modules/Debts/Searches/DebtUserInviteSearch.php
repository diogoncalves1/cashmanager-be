<?php

namespace Modules\Debts\Searches;

use App\Searches\ModuleSearch;

class DebtUserInviteSearch extends ModuleSearch
{
    protected array $fields = [];

    protected array $relations = ['sender' => ['users.name'], 'user' => ['users.name'], 'debt' => ['name']];
}
