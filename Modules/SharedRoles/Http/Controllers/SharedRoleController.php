<?php
namespace Modules\SharedRoles\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Modules\Language\Repositories\LanguageRepository;
use Modules\SharedRoles\DataTables\SharedRoleDataTable;
use Modules\SharedRoles\Http\Requests\SharedRolePermissionsRequest;
use Modules\SharedRoles\Http\Requests\SharedRoleRequest;
use Modules\SharedRoles\Repositories\SharedPermissionRepository;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class SharedRoleController extends ApiController
{
    protected SharedRoleRepository $repository;
    private SharedPermissionRepository $sharedPermissionRepository;
    private LanguageRepository $languageRepository;

    public function __construct(SharedRoleRepository $repository, SharedPermissionRepository $sharedPermissionRepository, LanguageRepository $languageRepository)
    {
        $this->repository                 = $repository;
        $this->sharedPermissionRepository = $sharedPermissionRepository;
        $this->languageRepository         = $languageRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param SharedRoleDataTable
     * @throws AuthorizationException
     */
    public function index(SharedRoleDataTable $dataTable)
    {
        $this->allowedAction('viewSharedRole');

        return $dataTable->render("sharedroles::shared-roles.index");
    }

    /**
     * Show the form for create a new resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function create(): Renderable
    {
        $this->allowedAction('createSharedRole');

        $languages = $this->languageRepository->all();

        return view("sharedroles::shared-roles.create", compact('languages'));
    }

    /**
     * Store a newly created resource in storage.
     * @param SharedRoleRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(SharedRoleRequest $request): RedirectResponse
    {
        $this->allowedAction('createSharedRole');

        $this->repository->store($request);

        return redirect()->route('admin.shared-roles.index');
    }

    /**
     * Show the form for edit a resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function edit(string $id): Renderable
    {
        $this->allowedAction('editSharedRole');

        $languages  = $this->languageRepository->all();
        $sharedRole = $this->repository->show($id);

        return view("sharedroles::shared-roles.create", compact('languages', 'sharedRole'));
    }

    /**
     * Update the specified resource in storage.
     * @param SharedRoleRequest $request
     * @param string $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(SharedRoleRequest $request, string $id): RedirectResponse
    {
        $this->allowedAction('editSharedRole');

        $this->repository->update($request, $id);

        return redirect()->route('admin.shared-roles.index');
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->allowedAction('destroySharedRole');

            $sharedRole = $this->repository->destroy($id);

            return $this->ok(message: "Papel de partilha {$sharedRole->name->en} apagado com sucesso!");
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao tentar apagar papel de partilha.', $e, 500);
        }
    }

    /**
     * Show the form for manage a resource permissions.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function showManageForm(string $id): Renderable
    {
        $this->allowedAction('manageSharedRolePermission');

        $sharedRole               = $this->repository->show($id);
        $sharedPermissionsGrouped = $this->sharedPermissionRepository->allGroupedByCategory();

        $SharedRolePermissionsIds = $sharedRole->permissions->pluck('id')->toArray();

        return view("sharedroles::shared-roles.manage", compact('sharedPermissionsGrouped', 'SharedRolePermissionsIds', 'sharedRole'));
    }

    /**
     * Update the specified resource in storage.
     * @param SharedRolePermissionsRequest $request
     * @param string $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function manage(SharedRolePermissionsRequest $request, string $id): RedirectResponse
    {
        $this->allowedAction('manageSharedRolePermission');

        $this->repository->updateRolePermissions($request, $id);

        return redirect()->route('admin.shared-roles.index');
    }
}
