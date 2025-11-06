<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\ApiController;
use Modules\User\Http\Requests\UpdateUserRolesRequest;
use Modules\User\Http\Requests\UserRequest;
use Modules\User\Repositories\UserRepository;
use Illuminate\Http\Request;
use Modules\Permission\Repositories\RoleRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\User\DataTables\UserDataTable;

class UserController extends ApiController
{
    private UserRepository $repository;
    private RoleRepository $roleRepository;

    public function __construct(UserRepository $repository, RoleRepository $roleRepository)
    {
        $this->repository = $repository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Display a listing of the resource.
     * @param FinancialGoalContributionDataTable $dataTable
     */
    public function index(UserDataTable $dataTable)
    {
        $this->allowedAction('viewUser');

        return $dataTable->render('user::index');

        return view('user::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function create(): Renderable
    {
        $this->allowedAction('createUser');

        $roles = $this->roleRepository->all();

        return view('user::create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     * @param UserRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $this->allowedAction('createUser');

        $this->repository->store($request);

        return redirect()->route('admin.users.index');
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Renderable
     * @throws AuthorizationException
     */
    public function edit(string $id): Renderable
    {
        $this->allowedAction('editUser');

        $user = $this->repository->show($id);
        $roles = $this->roleRepository->all();
        $userRolesIds = $this->repository->getUserRolesIds($id);

        return view('user::create', compact('user', 'roles', 'userRolesIds'));
    }

    /**
     * Update the specified resource in storage.
     * @param UserRequest $request
     * @param string $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(UserRequest $request, string $id): RedirectResponse
    {
        $this->allowedAction('editUser');

        $this->repository->update($request, $id);

        return redirect()->route('admin.users.index');
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {

        try {
            $this->allowedAction('destroyUser');

            $this->repository->destroy($id);

            return $this->ok(message: "Utilizador apagado com sucesso!");
        } catch (\Exception $e) {
            return $this->fail("Erro ao apagar utilizador", $e, 500);
        }
    }

    /**
     * Show the form for manage the specified resource.
     * @param string $id
     * @return Renderable
     * @throws AuthorizationException
     */
    public function showManageForm(string $id): Renderable
    {
        $this->allowedAction('manageUserRoles');

        $userRolesIds = $this->repository->getUserRolesIds($id);

        $roles = $this->roleRepository->all();

        return view('user::manage', compact('userRolesIds', 'roles', 'id'));
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateUserRolesRequest $request
     * @param string $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function manage(UpdateUserRolesRequest $request, string $id): RedirectResponse
    {
        $this->allowedAction('manageUserRoles');

        $this->repository->manageRoles($request, $id);

        return redirect()->route('admin.users.index');
    }
}
