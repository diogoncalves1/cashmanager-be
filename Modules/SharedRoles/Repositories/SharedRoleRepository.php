<?php

namespace Modules\SharedRoles\Repositories;

use Modules\SharedRoles\Entities\SharedRole;
use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SharedRoleRepository implements RepositoryInterface
{
    public function all()
    {
        return SharedRole::all();
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $input = $request->only('code', 'name');

                $sharedRole = SharedRole::create($input);

                Log::info('Shared Role ' . $sharedRole->id . ' successfully created.');
                Session::flash('success', 'Papel de partilha adicionado com sucesso.');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao tentar criar papel de partilha.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $sharedRole = $this->show($id);

                $input = $request->only('code', 'name');

                $sharedRole->update($input);

                Log::info('Shared Role ' . $sharedRole->id . ' successfully updated.');
                Session::flash('success', "Papel de partilha atualizado com sucesso!");
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao tentar atualizar papel de partilha.');
        }
    }

    public function destroy(string $id)
    {
        return  DB::transaction(function () use ($id) {
            $sharedRole = $this->show($id);

            $sharedRole->delete();

            Log::info('Shared Role ' . $sharedRole->id . ' successfully deleted.');
            return $sharedRole;
        });
    }

    public function show(string $id)
    {
        return SharedRole::find($id);
    }

    public function updateRolePermissions(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $sharedRole = $this->show($id);

                $sharedRole->permissions()->sync($request->input('permissions', []));

                Log::info('Shared role permissions ' . $sharedRole->id . ' successfully updated.');
                Session::flash('success', "Permissões de partilha do papel de partilha atualizadas com sucesso!");
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', __("Error ao tentar atualizar o permissões de partilha do papel de partilha."));
        }
    }

    public function checkCode(Request $request)
    {
        $query = SharedRole::where('code', $request->get('code'));

        if ($request->get("id"))
            $query->where('id', '!=', $request->get('id'));

        $exists =  $query->exists();

        return $exists;
    }
}
