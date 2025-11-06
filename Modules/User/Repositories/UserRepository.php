<?php

namespace Modules\User\Repositories;

use Modules\User\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Repositories\RepositoryInterface;

class UserRepository implements RepositoryInterface
{
    public function all()
    {
        return User::whereDoesntHave('roles', function ($query) {
            $query->where('code', 'superAdmin');
        })->get();
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $input = $request->except(['roles', 'password']);

                $input['password'] = Hash::make($request->get('password'));

                $user = User::create($input);
                $this->updateRoles($user, $request->get('roles'));

                Log::info('User ' . $user->id . ' created');
                Session::flash('success', 'Utilizador criado com sucesso');

                return $user;
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Error ao adicionar utilizador');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $user = $this->show($id);

                $input = $request->except(['roles', 'password']);

                if ($request->get('password'))
                    $input['password'] = Hash::make($request->get('password'));

                $user->update($input);
                $this->updateRoles($user, $request->get('roles'));

                Log::info('User ' . $user->id . ' created');
                Session::flash('success', 'Utilizador criado com sucesso');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Error ao adicionar utilizador');
        }
    }

    public function show(string $id)
    {
        return User::find($id);
    }

    public function destroy(string $id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $user = $this->show($id);
                $user->delete();

                Log::info('User ' . $user->id . ' destroyed.');

                return 1;
            });
        } catch (\Exception $e) {
            Log::error($e);
            return 0;
        }
    }

    public function getUserRolesIds(string $id): array
    {
        try {
            $user = $this->show($id);

            $userRolesIds = $user->roles->pluck('id')->toArray();

            return $userRolesIds;
        } catch (\Exception $e) {
            Log::error($e);

            return [];
        }
    }

    public function manageRoles(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $input = $request->get('roles');

                $user = $this->show($id);

                $this->updateRoles($user, $input);

                Log::info('User ' . $user->id . ' updated roles.');
                Session::flash('success', 'Perfis do utilizador atualizados com sucesso');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao tentar atualizar os perfis do utilizador');
        }
    }

    public function updateRoles(User $user, ?array $roles = null)
    {
        try {
            DB::transaction(function () use ($user, $roles) {
                $user->roles()->sync($roles);
            });
        } catch (\Exception $e) {
            Log::error($e);
        }
    }
}
