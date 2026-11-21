<?php

namespace Modules\Friends\Searches;

use App\Searches\ModuleSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FriendshipRequestSearch extends ModuleSearch
{
    protected array $fields = [];

    protected array $relations = ['sender' => ['users.name', 'users.id'], 'receiver' => ['users.name', 'users.id']];

    protected function constrainRelation(Builder $query, Request $request, string $relation): void
    {
        $query->where('users.id', '!=', $request->user()->id);
    }
}
