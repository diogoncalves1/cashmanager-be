<?php
namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\Http\Requests\InviteUserAccountRequest;
use Modules\Accounts\Http\Resources\AccountUserResource;
use Modules\Accounts\Repositories\AccountUserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Finished: true
 */
class AccountUserController extends ApiController
{
    private AccountUserRepository $repository;

    public function __construct(AccountUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Remove user from account.
     * @param Request $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function revokeUser(Request $request, string $id, string $userId): JsonResponse
    {
        try {
            $relation = $this->repository->revokeUser($request, $id, $userId);

            return $this->ok(message: __('accounts::messages.account-users.revokeUser', ['userName' => $relation->user->name, 'accountName' => $relation->account->name]));
        } catch (Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Update user role from account.
     * @param Request $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function updateUserRole(InviteUserAccountRequest $request, string $id, string $userId): JsonResponse
    {
        try {
            $relation = $this->repository->updateUserRole($request, $id, $userId);

            return $this->ok(new AccountUserResource($relation), __('accounts::messages.account-users.updateUserRole', ['accountName' => $relation->account->name, 'userName' => $relation->user->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Leave user role from account.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function leave(Request $request, string $id): JsonResponse
    {
        try {
            $relation = $this->repository->leave($request, $id);

            return $this->ok(message: __('accounts::messages.account-users.leave', ['accountName' => $relation->account->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }
}
