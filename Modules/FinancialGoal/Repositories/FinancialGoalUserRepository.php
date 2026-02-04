<?php
namespace Modules\FinancialGoal\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Modules\FinancialGoal\Entities\FinancialGoalUser;
use Modules\SharedRoles\Exceptions\RelationNotExistsException;
use Modules\SharedRoles\Exceptions\SelfRoleUpdateNotAllowedException;
use Modules\SharedRoles\Exceptions\UnauthorizedUpdateUserRoleException;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class FinancialGoalUserRepository
{
    private $financialGoalRepository;
    private $sharedRoleRepository;
    protected ActivityLogRepository $activityRepo;

    public function __construct(FinancialGoalRepository $financialGoalRepository, SharedRoleRepository $sharedRoleRepository, ActivityLogRepository $activityRepo)
    {
        $this->financialGoalRepository = $financialGoalRepository;
        $this->sharedRoleRepository    = $sharedRoleRepository;
        $this->activityRepo            = $activityRepo;
    }

    public function revokeUser(Request $request, string $id, $userId)
    {
        return DB::transaction(function () use ($request, $id, $userId) {
            $financialGoal = $this->financialGoalRepository->show($id);

            $user = $request->user();

            $sharedRole       = $financialGoal->userSharedRole($financialGoal, $user->id);
            $sharedRoleInvite = $financialGoal->userSharedRole($financialGoal, $userId);

            if (! $this->hasRelation($userId, $id)) {
                throw new RelationNotExistsException();
            }
            if ($sharedRoleInvite->code == "creator") {
                throw new \Modules\SharedRoles\Exceptions\CreatorRevokeException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageFinancialGoalUsers')) {
                throw new \Modules\SharedRoles\Exceptions\UnauthorizedRevokeUserException();
            }
            if ($this->isSelf($userId, $user->id)) {
                throw new \Exception();
            }

            $relation = $this->destroy($userId, $id);
            $this->activityRepo->storeActivity($id, $user->id, 'user_removed', ['userId' => $userId, 'role' => $sharedRoleInvite->code]);

            return $relation;
        });
    }

    public function updateUserRole(Request $request, string $id, string $userId)
    {
        return DB::transaction(function () use ($request, $id, $userId) {
            $financialGoal = $this->financialGoalRepository->show($id);

            $user = $request->user();

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            $sharedRoleUserUpdate  = $financialGoal->userSharedRole($financialGoal, $userId);
            $newSharedRoleToUpdate = $this->sharedRoleRepository->show($request->get("shared_role_id"));

            if (! $this->hasRelation($userId, $id)) {
                throw new RelationNotExistsException();
            }
            if ($this->isSelf($userId, $user->id)) {
                throw new SelfRoleUpdateNotAllowedException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageFinancialGoalUsers') || $sharedRoleUserUpdate->code == "creator") {
                throw new UnauthorizedUpdateUserRoleException();
            }
            if ($newSharedRoleToUpdate->code == "creator") {
                throw new \Modules\FinancialGoal\Exceptions\FinancialGoalUser\SingleFinancialGoalCreatorViolationException();
            }
            if ($sharedRole->code == $sharedRoleUserUpdate->code) {
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
            $financialGoal = $this->financialGoalRepository->show($id);

            $user       = $request->user();
            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $this->hasRelation($user->id, $id)) {
                throw new RelationNotExistsException();
            }
            if ($sharedRole->code == 'creator') {
                throw new \Modules\SharedRoles\Exceptions\CreatorCantLeaveException();
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
    private function destroy(string $userId, string $financialGoalId)
    {
        $relation = FinancialGoalUser::query()->user($userId)->financialGoal($financialGoalId)->first();

        $relation->delete();

        return $relation;
    }
    private function update(string $userId, string $financialGoalId, array $input)
    {
        $relation = FinancialGoalUser::query()->user($userId)->financialGoal($financialGoalId)->first();

        $relation->update($input);

        return $relation;
    }

    // Extra Methods
    public function store(array $input)
    {
        return FinancialGoalUser::create($input);
    }
    public function hasRelation(string $userId, string $financialGoalId)
    {
        return FinancialGoalUser::query()->user($userId)->financialGoal($financialGoalId)->exists();
    }
}
