<?php

namespace App\Paginates;

use App\Filters\ModuleFilter;
use App\Searches\ModuleSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Modules\User\Entities\User;

abstract class ModulePaginate
{
    protected array $allowedOrderFields = [];

    protected string $defaultOrderField = 'id';

    protected string $defaultSort = 'asc';

    protected array $tieBreakers = [];

    public function __construct(protected ModuleFilter $filter, protected ModuleSearch $search) {}

    abstract protected function query(User $user, Request $request): Builder|Relation;

    public function paginateByUser(User $user, Request $request, ?int $perPage = null): LengthAwarePaginator
    {
        $validated = validator($request->query(), [
            'start' => ['prohibited'], 'length' => ['prohibited'], 'columns' => ['prohibited'],
            'order' => ['prohibited'], 'draw' => ['prohibited'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('app.api_pagination.max_per_page', 100)],
            'order_by' => ['sometimes', 'string', Rule::in(array_keys($this->allowedOrderFields))],
            'sort_by' => ['sometimes', Rule::in(['asc', 'desc'])],
        ])->validate();
        $size = $validated['per_page'] ?? $perPage ?? config('app.api_pagination.per_page', 10);
        validator(['per_page' => $size], ['per_page' => ['integer', 'min:1', 'max:'.config('app.api_pagination.max_per_page', 100)]])->validate();
        $query = $this->filteredQuery($user, $request);
        $column = isset($validated['order_by']) ? $this->allowedOrderFields[$validated['order_by']] : $this->defaultOrderField;
        $direction = $validated['sort_by'] ?? $this->defaultSort;
        $query->reorder()->orderBy($column, $direction);
        // Equal sort values must not move between pages.
        if ($column !== $this->defaultOrderField) {
            $query->orderBy($this->defaultOrderField, $direction);
        }
        foreach ($this->tieBreakers as $tieBreaker) {
            $query->orderBy($tieBreaker, $direction);
        }

        return $query->paginate((int) $size, ['*'], 'page', (int) ($validated['page'] ?? 1));
    }

    public function filteredQuery(User $user, Request $request): Builder|Relation
    {
        $query = $this->query($user, $request);

        return $this->search->search($request, $this->filter->filter($request, $query));
    }

    protected function visibleTo(Builder $query, User $user, string $pivot, string $foreignKey, string $column, string $permission): Builder
    {
        return $query->whereExists(function ($membership) use ($user, $pivot, $foreignKey, $column, $permission) {
            $membership->selectRaw('1')->from($pivot.' as membership')
                ->join('shared_permission_roles as rp', 'rp.shared_role_id', '=', 'membership.shared_role_id')
                ->join('shared_permissions as permission', 'permission.id', '=', 'rp.shared_permission_id')
                ->whereColumn('membership.'.$foreignKey, $column)
                ->where('membership.user_id', $user->id)
                ->where('permission.code', $permission);
        });
    }
}
