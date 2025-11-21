<?php
namespace Modules\Accounts\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Entities\AccountUser;
use Modules\SharedRoles\Exceptions\RelationNotExistsException;
use Modules\SharedRoles\Exceptions\SelfRoleUpdateNotAllowedException;
use Modules\SharedRoles\Exceptions\UnauthorizedUpdateUserRoleException;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class AccountUserRepository
{
    private $accountRepository;
    private $sharedRoleRepository;

    public function __construct(AccountRepository $accountRepository, SharedRoleRepository $sharedRoleRepository)
    {
        $this->accountRepository    = $accountRepository;
        $this->sharedRoleRepository = $sharedRoleRepository;
    }

    public function revokeUser(Request $request, string $id, $userId)
    {
        return DB::transaction(function () use ($request, $id, $userId) {
            $account = $this->accountRepository->show($id);

            $user = $request->user();

            $sharedRole       = $account->userSharedRole($account, $user->id);
            $sharedRoleInvite = $account->userSharedRole($account, $userId);

            if (! $this->hasRelation($userId, $id)) {
                throw new RelationNotExistsException();
            }
            if ($sharedRoleInvite->code == "creator") {
                throw new \Modules\SharedRoles\Exceptions\CreatorRevokeException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageAccountUsers')) {
                throw new \Modules\SharedRoles\Exceptions\UnauthorizedRevokeUserException();
            }
            if ($this->isSelf($userId, $user->id)) {
                throw new \Exception();
            }

            $relation = $this->destroy($userId, $id);

            return $relation;
        });
    }

    public function updateUserRole(Request $request, string $id, string $userId)
    {
        return DB::transaction(function () use ($request, $id, $userId) {
            $account = $this->accountRepository->show($id);

            $user = $request->user();

            $sharedRole = $account->userSharedRole($account, $user->id);

            $sharedRoleUserUpdate  = $account->userSharedRole($account, $userId);
            $newSharedRoleToUpdate = $this->sharedRoleRepository->show($request->get("shared_role_id"));

            if (! $this->hasRelation($userId, $id)) {
                throw new RelationNotExistsException();
            }
            if ($this->isSelf($userId, $user->id)) {
                throw new SelfRoleUpdateNotAllowedException();
            }
            if ($newSharedRoleToUpdate->code == "creator") {
                throw new \Modules\Accounts\Exceptions\SingleAccountCreatorViolationException();
            }
            if ($sharedRole->code == $sharedRoleUserUpdate->code) {
                throw new UnauthorizedUpdateUserRoleException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageAccountUsers') || $sharedRoleUserUpdate->code == "creator") {
                throw new UnauthorizedUpdateUserRoleException();
            }

            $input = $request->only(["shared_role_id"]);

            $relation = $this->update($userId, $id, $input);

            return $relation;
        });
    }

    public function leave(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $account = $this->accountRepository->show($id);

            $user       = $request->user();
            $sharedRole = $account->userSharedRole($account, $user->id);

            if ($sharedRole->code == 'creator') {
                throw new \Modules\SharedRoles\Exceptions\CreatorCantLeaveException();
            }
            if (! $this->hasRelation($user->id, $id)) {
                throw new RelationNotExistsException();
            }

            $relation = $this->destroy($user->id, $id);

            return $relation;
        });
    }

    // Private Methods
    private function isSelf(string $receiverId, string $userId)
    {
        return $receiverId == $userId;
    }
    private function destroy(string $userId, string $accountId)
    {
        $relation = AccountUser::query()->user($userId)->account($accountId)->first();

        $relation->delete();

        return $relation;
    }
    private function update(string $userId, string $accountId, array $input)
    {
        $relation = AccountUser::query()->user($userId)->account($accountId)->first();

        $relation->update($input);

        return $relation;
    }

    // Extra Methods
    public function store(array $input)
    {
        return AccountUser::create($input);
    }
    public function hasRelation(string $userId, string $accountId)
    {
        return AccountUser::query()->user($userId)->account($accountId)->exists();
    }
}
