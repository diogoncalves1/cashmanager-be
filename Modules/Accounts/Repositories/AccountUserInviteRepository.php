<?php
namespace Modules\Accounts\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Entities\AccountUserInvite;
use Modules\Friends\Repositories\FriendshipRepository;
use Modules\SharedRoles\Exceptions\AlreadyRelationException;
use Modules\SharedRoles\Exceptions\InviteNotFoundException;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class AccountUserInviteRepository
{
    private $accountRepository;
    private $friendshipRepository;
    private $sharedRoleRepository;
    private $accountUserRepo;

    public function __construct(AccountRepository $accountRepository, SharedRoleRepository $sharedRoleRepository, FriendshipRepository $friendshipRepository, AccountUserRepository $accountUserRepo)
    {
        $this->accountRepository    = $accountRepository;
        $this->sharedRoleRepository = $sharedRoleRepository;
        $this->friendshipRepository = $friendshipRepository;
        $this->accountUserRepo      = $accountUserRepo;
    }

    public function invite(Request $request, string $id, string $userId)
    {
        return DB::transaction(function () use ($request, $id, $userId) {
            $account = $this->accountRepository->show($id);

            $user             = $request->user();
            $sharedRole       = $account->userSharedRole($account, $user->id);
            $sharedRoleInvite = $this->sharedRoleRepository->show($request->get('shared_role_id'));

            if ($sharedRoleInvite->code == "creator") {
                throw new \Modules\SharedRoles\Exceptions\CreatorInviteException();
            }
            if (! $this->friendshipRepository->areFriends($user->id, $userId)) {
                throw new \Modules\Friends\Exceptions\FriendshipNotFoundException();
            }
            if ($this->isSelf($userId, $user->id)) {
                throw new \Modules\SharedRoles\Exceptions\SelfInviteException();
            }
            if ($this->exceededDeclines($userId, $id, 3, 30)) {
                throw new \Modules\SharedRoles\Exceptions\InvitesLimitExceededException();
            }
            if ($this->hasPendingRequest($userId, $id)) {
                throw new \Modules\SharedRoles\Exceptions\InviteAlreadySentException();
            }
            if ($this->accountUserRepo->hasRelation($userId, $id)) {
                throw new AlreadyRelationException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageAccountUsers')) {
                throw new \Modules\SharedRoles\Exceptions\InviteUserNotAllowedException();
            }

            $input               = $request->only(['shared_role_id']);
            $input['account_id'] = $id;
            $input['user_id']    = $userId;
            $input['status']     = 'pending';

            $invite = AccountUserInvite::create($input);

            return $invite;
        });
    }

    public function accept(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            if ($this->accountUserRepo->hasRelation($user->id, $id)) {
                throw new AlreadyRelationException();
            }
            if (! $this->hasPendingRequest($user->id, $id)) {
                throw new InviteNotFoundException();
            }

            $invite = AccountUserInvite::query()->user($user->id)->account($id)->status("pending")->first();

            $invite->delete();

            $input    = ["account_id" => $id, "user_id" => $user->id, "shared_role_id" => $invite->shared_role_id];
            $relation = $this->accountUserRepo->store($input);

            return $relation;
        });

    }

    public function destroyInvite(Request $request, string $id, string $userId)
    {
        return DB::transaction(function () use ($request, $id, $userId) {
            $account = $this->accountRepository->show($id);

            $user = $request->user();

            $sharedRole = $account->userSharedRole($account, $user->id);

            if (! $this->hasPendingRequest($userId, $id)) {
                throw new InviteNotFoundException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageAccountUsers')) {
                throw new \Modules\SharedRoles\Exceptions\UnauthorizedDestroyInviteException();
            }

            $invite = $this->destroy($userId, $id, "pending");

            return $invite;
        });
    }

    public function revoke(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $account = $this->accountRepository->show($id);

            $user = $request->user();

            if (! $this->hasPendingRequest($user->id, $id)) {
                throw new InviteNotFoundException();
            }

            $input = ["status" => "revoked"];

            $invite = $this->update($user->id, $id, "pending", $input);

            return $invite;
        });
    }

    // Private Methods
    private function isSelf(string $receiverId, string $userId)
    {
        return $receiverId == $userId;
    }
    private function exceededDeclines(string $receiverId, string $accountId, int $maxDeclines, int $days)
    {
        $limitDate = Helpers::getOldDate($days);

        return AccountUserInvite::query()->user($receiverId)->account($accountId)->status('revoked')->where('created_at', '>=', $limitDate)->count() >= $maxDeclines;
    }
    private function hasPendingRequest(string $receiverId, string $accountId)
    {
        return AccountUserInvite::query()->user($receiverId)->account($accountId)->status('pending')->exists();
    }
    private function destroy(string $userId, string $accountId, string $status)
    {
        $invite = AccountUserInvite::query()->user($userId)->account($accountId)->status($status)->first();

        $invite->delete();

        return $invite;
    }
    private function update(string $userId, string $accountId, string $status, array $input)
    {
        $invite = AccountUserInvite::query()->user($userId)->account($accountId)->status($status)->first();

        $invite->update($input);

        return $invite;
    }
}
