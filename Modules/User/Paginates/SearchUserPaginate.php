<?php

namespace Modules\User\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\User\Entities\User;
use Modules\User\Filters\SearchUserFilter;
use Modules\User\Http\Resources\Lists\SearchUserListResource;
use Modules\User\Searches\SearchUserSearch;

class SearchUserPaginate extends ModulePaginate
{
    public const RESOURCE = SearchUserListResource::class;

    protected array $allowedOrderFields = ['id' => 'users.id', 'name' => 'users.name', 'username' => 'users.username'];

    protected string $defaultOrderField = 'users.id';

    protected string $defaultSort = 'desc';

    public function __construct(SearchUserFilter $filter, SearchUserSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return User::query()->where('users.id', '!=', $user->id);
    }
}
