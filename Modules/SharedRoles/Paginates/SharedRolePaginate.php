<?php

namespace Modules\SharedRoles\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\SharedRoles\Filters\SharedRoleFilter;
use Modules\SharedRoles\Http\Resources\SharedRoleResource;
use Modules\SharedRoles\Searches\SharedRoleSearch;
use Modules\User\Entities\User;

class SharedRolePaginate extends ModulePaginate
{
    public const RESOURCE = SharedRoleResource::class;

    protected array $allowedOrderFields = ['id' => 'shared_roles.id', 'code' => 'shared_roles.code'];

    protected string $defaultOrderField = 'shared_roles.id';

    protected string $defaultSort = 'asc';

    public function __construct(SharedRoleFilter $filter, SharedRoleSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        Gate::authorize('authorization', 'viewSharedRole');

        return SharedRole::query();
    }
}
