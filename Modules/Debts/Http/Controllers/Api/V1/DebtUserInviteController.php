<?php
namespace Modules\Debts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Debts\Http\Requests\InviteUserDebtRequest;
use Modules\Debts\Http\Resources\DebtUserInvites\DebtUserInviteResource;
use Modules\Debts\Http\Resources\DebtUser\DebtUserResource;
use Modules\Debts\Repositories\DebtUserInviteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class DebtUserInviteController extends ApiController
{
    private DebtUserInviteRepository $repository;

    public function __construct(DebtUserInviteRepository $debtUserRepository)
    {
        $this->repository = $debtUserRepository;
    }

    /**
     * Invite the user for debt.
     * @param InviteUserDebtRequest $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function invite(InviteUserDebtRequest $request, string $id, string $userId): JsonResponse
    {
        try {
            $invite = $this->repository->invite($request, $id, $userId);

            return $this->ok(new DebtUserInviteResource($invite), __('debts::messages.debt-user-invites.invite', ['debtName' => $invite->debt->name, 'userName' => $invite->user->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }

    }

    /**
     * Accept debt invite.
     * @param InviteUserDebtRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function accept(Request $request, string $id): JsonResponse
    {
        try {
            $relation = $this->repository->accept($request, $id);

            return $this->ok(new DebtUserResource($relation), __('debts::messages.debt-user-invites.accept', ['name' => $relation->debt->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Destroy debt invite.
     * @param InviteUserDebtRequest $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id, string $userId): JsonResponse
    {
        try {
            $invite = $this->repository->destroyInvite($request, $id, $userId);

            return $this->ok(message: __('debts::messages.debt-user-invites.destroy', ['debtName' => $invite->debt->name, 'userName' => $invite->user->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Revoke debt invite.
     * @param InviteUserDebtRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function revoke(Request $request, string $id): JsonResponse
    {
        try {
            $invite = $this->repository->revoke($request, $id);

            return $this->ok(message: __('debts::messages.debt-user-invites.revoke', ['debtName' => $invite->debt->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
