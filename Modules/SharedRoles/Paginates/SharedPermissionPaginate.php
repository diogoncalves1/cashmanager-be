<?php

namespace Modules\SharedRoles\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\SharedRoles\Entities\SharedPermission;
use Modules\SharedRoles\Filters\SharedPermissionFilter;
use Modules\SharedRoles\Http\Resources\SharedPermissionResource;
use Modules\SharedRoles\Searches\SharedPermissionSearch;
use Modules\User\Entities\User;

class SharedPermissionPaginate extends ModulePaginate
{
    public const RESOURCE = SharedPermissionResource::class;

    protected array $allowedOrderFields = ['id' => 'shared_permissions.id', 'code' => 'shared_permissions.code'];

    protected string $defaultOrderField = 'shared_permissions.id';

    protected string $defaultSort = 'asc';

    public function __construct(SharedPermissionFilter $filter, SharedPermissionSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        Gate::authorize('authorization', 'viewSharedPermission');

        return SharedPermission::query();
    }
}
