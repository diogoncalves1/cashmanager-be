<?php

namespace Modules\SharedRoles\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\SharedRoles\Http\Resources\SharedRoleCollection;
use Modules\SharedRoles\Paginates\SharedRolePaginate;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class SharedRoleController extends ApiController
{
    protected SharedRoleRepository $sharedRoleRepository;

    public function __construct(SharedRoleRepository $sharedRoleRepository)
    {
        $this->sharedRoleRepository = $sharedRoleRepository;
    }

    public function all()
    {
        try {
            $sharedRoles = $this->sharedRoleRepository->all();

            return $this->ok(new SharedRoleCollection($sharedRoles));
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    public function index(Request $request, SharedRolePaginate $paginate): JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            return $this->respondModulePaginated($paginate, $request);
        });
    }

    public function checkRoleCode(Request $request)
    {
        try {
            $this->allowedAction('viewSharedRole');

            $request->validate([
                'id' => 'nullable',
                'code' => 'required|string|max:255',
            ]);

            $exists = $this->sharedRoleRepository->checkCode($request);

            return $this->ok(additionals: ['exists' => $exists]);
        } catch (\Exception $e) {
        }
    }
}
