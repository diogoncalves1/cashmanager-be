<?php

namespace Modules\Permission\Repositories;

use Modules\Permission\Entities\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Repositories\RepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Modules\Permission\Entities\Permission;

class PermissionRepository implements RepositoryInterface
{
    protected RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function all()
    {
        return Permission::where('visible', 1)->orderBy('name', 'asc')->get();
    }

    public function allAndNotVisible()
    {
        return Permission::orderBy('name', 'asc')->get();
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $role = $this->roleRepository->showByCode('superAdmin');
                $input = $request->except('visible');

                if ($request->get('visible') && $request->get('visible') == 1)
                    $input['visible'] = 1;
                else
                    $input['visible'] = 0;

                $permission = Permission::create($input);
                $role->permissions()->attach($permission->id);

                if ($permission->visible == 1) {
                    $role = $this->roleRepository->showByCode('admin');
                    $role->permissions()->attach($permission->id);
                }

                Log::info('Permission ' . $permission->id . ' added.');
                Session::flash('success', 'Permissão adicionada com sucesso!');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao adicionar permissão.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $input = $request->all();

            DB::transaction(function () use ($input, $id) {
                $permission = $this->show($id);
                $permission->update($input);

                Log::info('Permission ' . $permission->id . ' updated.');
                Session::flash('success', 'Permissão atualizada com sucesso!');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao tentar atualizar permissão.');
        }
    }

    public function destroy(string $id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $permission = $this->show($id);

                if (!$permission)
                    return 0;

                $permission->delete();

                Log::info('Permission ' . $permission->id . ' destroyed.');

                return 1;
            });
        } catch (\Exception $e) {
            Log::error($e);
            return 0;
        }
    }

    public function show(string $id)
    {
        return Permission::find($id);
    }

    public function dataTable(Request $request)
    {
        $query = Permission::where('visible', 1);
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where("name", 'like', "{$search}%")
                    ->orWhere("category", 'like', "{$search}%")
                    ->orWhere("code", 'like', "{$search}%");
            });
        }

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumn = $request->input("columns.$orderColumnIndex.data");
        $orderDir = $request->input('order.0.dir');
        if ($orderColumn && $orderDir) {
            $query->orderBy($orderColumn, $orderDir);
        }

        $total = $query->count();

        $permissions = $query->offset($request->start)
            ->limit($request->length)
            ->select("name", "category", 'id', 'code',)
            ->get();

        foreach ($permissions as &$permission) {
            $permission->actions = "<div class='btn-group'>
                            <a type='button' href='" . route('admin.permissions.edit', $permission->id) . "' class='btn mr-1 btn-default'>
                                <i class='fas fa-edit'></i>
                            </a>
                            <button type='button' onclick='modalDelete({$permission->id})' class='btn btn-default'>
                                <i class='fas fa-trash'></i>
                            </button>
                        </div>";
        }

        $data = [
            'draw' => intval($request->draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $permissions
        ];

        return $data;
    }

    public function getPermissionsGrouped()
    {
        try {
            if (Auth::user()->can('authorization', 'superAdmin'))
                $permissions = $this->allAndNotVisible();
            else
                $permissions = $this->all();

            return $permissions->groupBy('category');
        } catch (\Exception $e) {
            Log::error($e);

            return [];
        }
    }
}
