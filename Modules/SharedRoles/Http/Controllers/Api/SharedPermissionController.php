<?php

namespace Modules\SharedRoles\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\SharedRoles\Repositories\SharedPermissionRepository;
use Illuminate\Http\Request;
use Modules\SharedRoles\DataTables\SharedPermissionDataTable;

class SharedPermissionController extends ApiController
{
    private SharedPermissionRepository $sharedPermissionRepository;

    public function __construct(SharedPermissionRepository $sharedPermissionRepository)
    {
        $this->sharedPermissionRepository = $sharedPermissionRepository;
    }

    public function index(SharedPermissionDataTable $dataTable)
    {
        try {
            return $dataTable->ajax();
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    public function checkPermissionCode(Request $request): JsonResponse
    {
        try {
            $this->allowedAction('viewSharedPermission');

            $request->validate([
                "id" => "nullable",
                "code" => "required|string|max:100",
            ]);

            $exists = $this->sharedPermissionRepository->checkPermissionCode($request);

            return $this->ok(additionals: ['exists' => $exists]);
        } catch (\Exception $e) {
            return $this->fail('', $e, 500);
        }
    }
}
