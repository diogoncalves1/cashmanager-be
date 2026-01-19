<?php
namespace Modules\Debts\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Debts\Entities\DebtUser;
use Modules\SharedRoles\Exceptions\RelationNotExistsException;
use Modules\SharedRoles\Exceptions\SelfRoleUpdateNotAllowedException;
use Modules\SharedRoles\Exceptions\UnauthorizedUpdateUserRoleException;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class DebtUserRepository
{
    private $debtRepository;
    private $sharedRoleRepository;

    public function __construct(DebtRepository $debtRepository, SharedRoleRepository $sharedRoleRepository)
    {
        $this->debtRepository       = $debtRepository;
        $this->sharedRoleRepository = $sharedRoleRepository;
    }

    public function revokeUser(Request $request, string $id, $userId)
    {
        return DB::transaction(function () use ($request, $id, $userId) {
            $debt = $this->debtRepository->show($id);

            $user = $request->user();

            $sharedRole       = $debt->userSharedRole($debt, $user->id);
            $sharedRoleInvite = $debt->userSharedRole($debt, $userId);

            if (! $this->hasRelation($userId, $id)) {
                throw new RelationNotExistsException();
            }
            if ($sharedRoleInvite->code == "creator") {
                throw new \Modules\SharedRoles\Exceptions\CreatorRevokeException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageDebtUsers')) {
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
            $debt = $this->debtRepository->show($id);

            $user = $request->user();

            $sharedRole = $debt->userSharedRole($debt, $user->id);

            $sharedRoleUserUpdate  = $debt->userSharedRole($debt, $userId);
            $newSharedRoleToUpdate = $this->sharedRoleRepository->show($request->get("shared_role_id"));

            if (! $this->hasRelation($userId, $id)) {
                throw new RelationNotExistsException();
            }
            if ($this->isSelf($userId, $user->id)) {
                throw new SelfRoleUpdateNotAllowedException();
            }
            if ($newSharedRoleToUpdate->code == "creator") {
                throw new \Modules\Debts\Exceptions\Debts\SingleDebtCreatorViolationException();
            }
            if ($sharedRole->code == $sharedRoleUserUpdate->code) {
                throw new UnauthorizedUpdateUserRoleException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageDebtUsers') || $sharedRoleUserUpdate->code == "creator") {
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
            $debt = $this->debtRepository->show($id);

            $user       = $request->user();
            $sharedRole = $debt->userSharedRole($debt, $user->id);

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
    private function destroy(string $userId, string $debtId)
    {
        $relation = DebtUser::query()->user($userId)->debt($debtId)->first();

        $relation->delete();

        return $relation;
    }
    private function update(string $userId, string $debtId, array $input)
    {
        $relation = DebtUser::query()->user($userId)->debt($debtId)->first();

        $relation->update($input);

        return $relation;
    }

    // Extra Methods
    public function store(array $input)
    {
        return DebtUser::create($input);
    }
    public function hasRelation(string $userId, string $debtId)
    {
        return DebtUser::query()->user($userId)->debt($debtId)->exists();
    }
}
