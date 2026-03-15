<?php
namespace Modules\Debts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Debts\Http\Requests\InviteUserDebtRequest;
use Modules\Debts\Http\Resources\DebtUser\DebtUserResource;
use Modules\Debts\Repositories\DebtUserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class DebtUserController extends ApiController
{
    private DebtUserRepository $repository;

    public function __construct(DebtUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Remove user from debt.
     * @param Request $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function revokeUser(Request $request, string $id, string $userId): JsonResponse
    {
        try {
            $relation = $this->repository->revokeUser($request, $id, $userId);

            return $this->ok(message: __('debts::messages.debt-users.revokeUser', ['userName' => $relation->user->name, 'debtName' => $relation->debt->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update user role from debt.
     * @param Request $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function updateUserRole(InviteUserDebtRequest $request, string $id, string $userId): JsonResponse
    {
        try {
            $relation = $this->repository->updateUserRole($request, $id, $userId);

            return $this->ok(new DebtUserResource($relation), __('debts::messages.debt-users.updateUserRole', ['debtName' => $relation->debt->name, 'userName' => $relation->user->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Leave user role from debt.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function leave(Request $request, string $id): JsonResponse
    {
        try {
            $relation = $this->repository->leave($request, $id);

            return $this->ok(message: __('debts::messages.debt-users.leave', ['debtName' => $relation->debt->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
