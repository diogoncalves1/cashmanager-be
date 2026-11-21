<?php

namespace Modules\SharedRoles\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\SharedRoles\Paginates\SharedPermissionPaginate;
use Modules\SharedRoles\Repositories\SharedPermissionRepository;

class SharedPermissionController extends ApiController
{
    private SharedPermissionRepository $sharedPermissionRepository;

    public function __construct(SharedPermissionRepository $sharedPermissionRepository)
    {
        $this->sharedPermissionRepository = $sharedPermissionRepository;
    }

    public function index(Request $request, SharedPermissionPaginate $paginate): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            return $this->respondModulePaginated($paginate, $request);
        });
    }

    public function checkPermissionCode(Request $request): JsonResponse
    {
        try {
            $this->allowedAction('viewSharedPermission');

            $request->validate([
                'id' => 'nullable',
                'code' => 'required|string|max:100',
            ]);

            $exists = $this->sharedPermissionRepository->checkPermissionCode($request);

            return $this->ok(additionals: ['exists' => $exists]);
        } catch (\Exception $e) {
            return $this->fail('', $e, 500);
        }
    }
}
