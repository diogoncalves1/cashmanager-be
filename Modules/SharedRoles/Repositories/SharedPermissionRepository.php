<?php

namespace Modules\SharedRoles\Repositories;

use Modules\SharedRoles\Entities\SharedPermission;
use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SharedPermissionRepository implements RepositoryInterface
{

    public function all()
    {
        return SharedPermission::all();
    }

    public function allGroupedByCategory()
    {
        $sharedPermissions = $this->all();

        return collect($sharedPermissions)->groupBy(function ($item) {
            return $item->category;
        });
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $input = $request->only(['code', 'category', 'name']);

                $sharedPermission = SharedPermission::create($input);

                Log::info('Shared permission ' . $sharedPermission->id . ' successfully created.');
                Session::flash('success', 'Permissão de partilha criada com sucesso!');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao tentar criar permissão de partilha.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $sharedPermission = $this->show($id);

                $input = $request->only(['code', 'category', 'name']);

                $sharedPermission->update($input);

                Log::info('Shared permission ' . $sharedPermission->id . ' successfully updated.');
                Session::flash('success', 'Permissão de partilha atualizada com sucesso!');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao tentar atualizar permissão de partilha.');
        }
    }

    public function destroy(string $id)
    {
        return DB::transaction(function () use ($id) {
            $sharedPermission = $this->show($id);

            $sharedPermission->delete();

            Log::info('Shared permission ' . $sharedPermission->id . ' successfully deleted.');
            return $sharedPermission;
        });
    }

    public function show(string $id)
    {
        return SharedPermission::find($id);
    }

    public function checkPermissionCode(Request $request)
    {
        $query = SharedPermission::where('code', $request->get('code'));

        if ($request->get("id"))
            $query->where('id', '!=', $request->get('id'));

        $exists = $query->exists();

        return $exists;
    }
}
