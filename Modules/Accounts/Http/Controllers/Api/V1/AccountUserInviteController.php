<?php

namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\Http\Requests\InviteUserAccountRequest;
use Modules\Accounts\Http\Resources\Accounts\AccountInvitationStatsResource;
use Modules\Accounts\Http\Resources\Accounts\AccountUserInviteResource;
use Modules\Accounts\Http\Resources\Accounts\AccountUserResource;
use Modules\Accounts\Paginates\AccountUserInvitePaginate;
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
     *
     * @param  InviteUserAccountRequest  $request
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
     *
     * @param  InviteUserAccountRequest  $request
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
     *
     * @param  InviteUserAccountRequest  $request
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

    /**
     * Return a invitations stats of all user financial goals.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function getInvitationsStats(Request $request)
    {
        try {
            $stats = $this->repository->getInvitationsStats($request);

            return $this->ok(new AccountInvitationStatsResource($stats));
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, $e->getCode());
        }
    }

    /**
     * Return all sent invitations stats of financial goals.
     *
     * @param  AccountUserInvitePaginate  $dataTable
     * @return JsonResponse
     */
    public function getSentInvitations(Request $request, AccountUserInvitePaginate $paginate): \Illuminate\Http\JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            $paginate->type = 'sent';

            return $this->respondModulePaginated($paginate, $request);
        });
    }

    /**
     * Return all received invitations stats of financial goals.
     *
     * @param  AccountUserInvitePaginate  $dataTable
     * @return JsonResponse
     */
    public function getReceivedInvitations(Request $request, AccountUserInvitePaginate $paginate): \Illuminate\Http\JsonResponse
    {
        return $this->safe(function () use ($request, $paginate) {
            $paginate->type = 'received';

            return $this->respondModulePaginated($paginate, $request);
        });
    }
}
