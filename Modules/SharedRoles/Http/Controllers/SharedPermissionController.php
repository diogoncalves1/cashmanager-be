<?php
namespace Modules\SharedRoles\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Modules\SharedRoles\DataTables\SharedPermissionDataTable;
use Modules\SharedRoles\Http\Requests\SharedPermissionRequest;
use Modules\SharedRoles\Repositories\SharedPermissionRepository;

class SharedPermissionController extends ApiController
{
    private SharedPermissionRepository $repository;

    public function __construct(SharedPermissionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param SharedRoleDataTable
     * @throws AuthorizationException
     */
    public function index(SharedPermissionDataTable $dataTable)
    {
        $this->allowedAction('viewSharedPermission');

        return $dataTable->render("sharedroles::shared-permissions.index");
    }

    /**
     * Show the form for create a new resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function create(): Renderable
    {
        $this->allowedAction('createSharedPermission');

        return view("sharedroles::shared-permissions.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SharedPermissionRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(SharedPermissionRequest $request): RedirectResponse
    {
        $this->allowedAction('createSharedPermission');

        $this->repository->store($request);

        return redirect()->route('admin.shared-permissions.index');
    }

    /**
     * Show the form for edit a resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function edit(string $id): Renderable
    {
        $this->allowedAction('editSharedPermission');

        $sharedPermission = $this->repository->show($id);

        return view("sharedroles::shared-permissions.create", compact('sharedPermission'));
    }

    /**
     * Update the specified resource in storage.
     * @param SharedPermissionRequest $request
     * @param string $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(SharedPermissionRequest $request, string $id): RedirectResponse
    {
        $this->allowedAction('editSharedPermission');

        $this->repository->update($request, $id);

        return redirect()->route('admin.shared-permissions.index');
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->allowedAction('destroySharedPermission');

            $sharedPermission = $this->repository->destroy($id);

            return $this->ok(message: "Permissão de partilha {$sharedPermission->name} apagada com sucesso!");
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao tentar apagar permissão de partilha.', $e);
        }
    }
}
