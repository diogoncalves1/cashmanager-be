<?php

namespace Modules\Permission\Http\Controllers;

use App\Http\Controllers\ApiController;
use Modules\Permission\Http\Requests\PermissionRequest;
use Modules\Permission\Repositories\PermissionRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Permission\DataTables\PermissionDataTable;

class PermissionController extends ApiController
{
    private $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function index(PermissionDataTable $dataTable)
    {
        $this->allowedAction('superAdmin');

        return $dataTable->render('permission::permissions.index');
    }

    public function create()
    {
        $this->allowedAction('superAdmin');

        return view('permission::permissions.create');
    }

    public function store(PermissionRequest $request)
    {
        $this->allowedAction('superAdmin');

        $this->permissionRepository->store($request);

        return redirect()->route('admin.permissions.index');
    }

    public function edit(string $id)
    {
        $this->allowedAction('superAdmin');

        $permission = $this->permissionRepository->show($id);

        return view('permission::permissions.create', compact('permission'));
    }

    public function update(PermissionRequest $request, string $id)
    {
        $this->allowedAction('superAdmin');

        $this->permissionRepository->update($request, $id);

        return redirect()->route('admin.permissions.index');
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->allowedAction('superAdmin');

            $permission = $this->permissionRepository->destroy($id);

            return $this->ok($permission, "Permissão apagada com sucesso!");
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail("Erro ao apagar permissão", $e, $e->getCode());
        }
    }

    public function dataTable(Request $request)
    {
        $data = $this->permissionRepository->dataTable($request);

        return response()->json($data);
    }
}
