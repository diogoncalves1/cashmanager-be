<?php
namespace Modules\FinancialGoal\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Core\Helpers;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Modules\FinancialGoal\Entities\FinancialGoalUserInvite;
use Modules\FinancialGoal\Repositories\FinancialGoalRepository;
use Modules\FinancialGoal\Repositories\FinancialGoalUserRepository;
use Modules\Friends\Repositories\FriendshipRepository;
use Modules\SharedRoles\Exceptions\AlreadyRelationException;
use Modules\SharedRoles\Exceptions\InviteNotFoundException;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class FinancialGoalUserInviteRepository
{
    private $financialGoalRepository;
    private $friendshipRepository;
    private $sharedRoleRepository;
    private $financialGoalUserRepo;
    protected ActivityLogRepository $activityRepo;

    public function __construct(FinancialGoalRepository $financialGoalRepository, SharedRoleRepository $sharedRoleRepository, FriendshipRepository $friendshipRepository, FinancialGoalUserRepository $financialGoalUserRepo, ActivityLogRepository $activityRepo)
    {
        $this->financialGoalRepository = $financialGoalRepository;
        $this->sharedRoleRepository    = $sharedRoleRepository;
        $this->friendshipRepository    = $friendshipRepository;
        $this->financialGoalUserRepo   = $financialGoalUserRepo;
        $this->activityRepo            = $activityRepo;
    }

    public function invite(Request $request, string $id, string $userId)
    {
        return DB::transaction(function () use ($request, $id, $userId) {
            $financialGoal = $this->financialGoalRepository->show($id);

            $user             = $request->user();
            $sharedRole       = $financialGoal->userSharedRole($financialGoal, $user->id);
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
            if ($this->financialGoalUserRepo->hasRelation($userId, $id)) {
                throw new AlreadyRelationException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageFinancialGoalUsers')) {
                throw new \Modules\SharedRoles\Exceptions\InviteUserNotAllowedException();
            }

            $input                      = $request->only(['shared_role_id']);
            $input['financial_goal_id'] = $id;
            $input['user_id']           = $userId;
            $input['status']            = 'pending';

            $invite = FinancialGoalUserInvite::create($input);
            $this->activityRepo->storeActivity($input['financial_goal_id'], $user->id, 'financial_goal', ['type' => 'user_invited', 'invitedUserId' => $userId, 'sharedRoleId' => $sharedRoleInvite->id, 'inviteId' => $invite->id]);

            return $invite;
        });
    }

    public function accept(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            if ($this->financialGoalUserRepo->hasRelation($user->id, $id)) {
                throw new AlreadyRelationException();
            }
            if (! $this->hasPendingRequest($user->id, $id)) {
                throw new InviteNotFoundException();
            }

            $invite = FinancialGoalUserInvite::query()->user($user->id)->financialGoal($id)->status("pending")->first();

            $invite->delete();

            $input    = ["financial_goal_id" => $id, "user_id" => $user->id, "shared_role_id" => $invite->shared_role_id];
            $relation = $this->financialGoalUserRepo->store($input);
            $this->activityRepo->storeActivity($input['financial_goal_id'], $user->id, 'user_joined', ['userId' => $user->id, 'role' => $relation->sharedRole->code, 'relationId' => $relation->id]);

            return $relation;
        });

    }

    public function destroyInvite(Request $request, string $id, string $userId)
    {
        return DB::transaction(function () use ($request, $id, $userId) {
            $financialGoal = $this->financialGoalRepository->show($id);

            $user = $request->user();

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $this->hasPendingRequest($userId, $id)) {
                throw new InviteNotFoundException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageFinancialGoalUsers')) {
                throw new \Modules\SharedRoles\Exceptions\UnauthorizedDestroyInviteException();
            }

            $invite = $this->destroy($userId, $id, "pending");

            return $invite;
        });
    }

    public function revoke(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $financialGoal = $this->financialGoalRepository->show($id);

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
    private function exceededDeclines(string $receiverId, string $financialGoalId, int $maxDeclines, int $days)
    {
        $limitDate = Helpers::getOldDate($days);

        return FinancialGoalUserInvite::query()->user($receiverId)->financialGoal($financialGoalId)->status('revoked')->where('created_at', '>=', $limitDate)->count() >= $maxDeclines;
    }
    private function hasPendingRequest(string $receiverId, string $financialGoalId)
    {
        return FinancialGoalUserInvite::query()->user($receiverId)->financialGoal($financialGoalId)->status('pending')->exists();
    }
    private function destroy(string $userId, string $financialGoalId, string $status)
    {
        $invite = FinancialGoalUserInvite::query()->user($userId)->financialGoal($financialGoalId)->status($status)->first();

        $invite->delete();

        return $invite;
    }
    private function update(string $userId, string $financialGoalId, string $status, array $input)
    {
        $invite = FinancialGoalUserInvite::query()->user($userId)->financialGoal($financialGoalId)->status($status)->first();

        $invite->update($input);

        return $invite;
    }
}
