<?php
namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\Http\Requests\InviteUserAccountRequest;
use Modules\Accounts\Http\Resources\AccountUserInviteResource;
use Modules\Accounts\Http\Resources\AccountUserResource;
use Modules\Accounts\Repositories\AccountUserInviteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Finished: True
 */
class AccountUserInviteController extends ApiController
{
    private AccountUserInviteRepository $repository;

    public function __construct(AccountUserInviteRepository $accountUserRepository)
    {
        $this->repository = $accountUserRepository;
    }

    /**
     * Invite the user for account.
     * @param InviteUserAccountRequest $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function invite(InviteUserAccountRequest $request, string $id, string $userId): JsonResponse
    {
        try {
            $invite = $this->repository->invite($request, $id, $userId);

            return $this->ok(new AccountUserInviteResource($invite), __('accounts::messages.account-user-invites.invite', ['accountName' => $invite->account->name, 'userName' => $invite->user->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }

    }

    /**
     * Accept account invite.
     * @param InviteUserAccountRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function accept(Request $request, string $id)
    {
        try {
            $relation = $this->repository->accept($request, $id);

            return $this->ok(new AccountUserResource($relation), __('accounts::messages.account-user-invites.accept', ['name' => $relation->account->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Destroy account invite.
     * @param InviteUserAccountRequest $request
     * @param string $id
     * @param string $userId
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id, string $userId)
    {
        try {
            $invite = $this->repository->destroyInvite($request, $id, $userId);

            return $this->ok(message: __('accounts::messages.account-user-invites.destroy', ['accountName' => $invite->account->name, 'userName' => $invite->user->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Revoke account invite.
     * @param InviteUserAccountRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function revoke(Request $request, string $id): JsonResponse
    {
        try {
            $invite = $this->repository->revoke($request, $id);

            return $this->ok(message: __('accounts::messages.account-user-invites.revoke', ['accountName' => $invite->account->name]));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail($e->getMessage(), $e, $e->getCode());
        }

    }
}
