<?php
namespace Modules\Debts\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Core\Helpers;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Modules\Debts\Entities\DebtUserInvite;
use Modules\Friends\Repositories\FriendshipRepository;
use Modules\SharedRoles\Exceptions\AlreadyRelationException;
use Modules\SharedRoles\Exceptions\InviteNotFoundException;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class DebtUserInviteRepository
{
    private $debtRepository;
    private $friendshipRepository;
    private $sharedRoleRepository;
    private $debtUserRepo;
    protected ActivityLogRepository $activityRepo;

    public function __construct(DebtRepository $debtRepository, SharedRoleRepository $sharedRoleRepository, FriendshipRepository $friendshipRepository, DebtUserRepository $debtUserRepo, ActivityLogRepository $activityRepo)
    {
        $this->debtRepository       = $debtRepository;
        $this->sharedRoleRepository = $sharedRoleRepository;
        $this->friendshipRepository = $friendshipRepository;
        $this->debtUserRepo         = $debtUserRepo;
        $this->activityRepo         = $activityRepo;
    }

    public function invite(Request $request, string $id, string $userId)
    {
        return DB::transaction(function () use ($request, $id, $userId) {
            $debt = $this->debtRepository->show($id);

            $user             = $request->user();
            $sharedRole       = $debt->userSharedRole($debt, $user->id);
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
            if ($this->debtUserRepo->hasRelation($userId, $id)) {
                throw new AlreadyRelationException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageDebtUsers')) {
                throw new \Modules\SharedRoles\Exceptions\InviteUserNotAllowedException();
            }

            $input            = $request->only(['shared_role_id']);
            $input['debt_id'] = $id;
            $input['user_id'] = $userId;
            $input['status']  = 'pending';

            $invite = DebtUserInvite::create($input);
            $this->activityRepo->storeActivity($debt->id, $user->id, 'debt', ['type' => 'user_invited', 'invitedUserId' => $userId, 'sharedRoleId' => $input['shared_role_id']]);

            return $invite;
        });
    }

    public function accept(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            if ($this->debtUserRepo->hasRelation($user->id, $id)) {
                throw new AlreadyRelationException();
            }
            if (! $this->hasPendingRequest($user->id, $id)) {
                throw new InviteNotFoundException();
            }

            $invite = DebtUserInvite::query()->user($user->id)->debt($id)->status("pending")->first();

            $input  = ["status" => "accepted"];
            $invite = $this->update($user->id, $id, "pending", $input);

            $input    = ["debt_id" => $id, "user_id" => $user->id, "shared_role_id" => $invite->shared_role_id];
            $relation = $this->debtUserRepo->store($input);
            $this->activityRepo->storeActivity($input['debt_id'], $user->id, 'debt', ['type' => 'user_joined', 'userId' => $user->id, 'sharedRoleId' => $invite->shared_role_id]);

            return $relation;
        });

    }

    public function destroyInvite(Request $request, string $id, string $userId)
    {
        return DB::transaction(function () use ($request, $id, $userId) {
            $debt = $this->debtRepository->show($id);

            $user = $request->user();

            $sharedRole = $debt->userSharedRole($debt, $user->id);

            if (! $this->hasPendingRequest($userId, $id)) {
                throw new InviteNotFoundException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('manageDebtUsers')) {
                throw new \Modules\SharedRoles\Exceptions\UnauthorizedDestroyInviteException();
            }

            $invite = $this->destroy($userId, $id, "pending");
            $this->activityRepo->storeActivity($id, $user->id, 'debt', ['type' => 'invited_destroyed', 'userId' => $userId]);

            return $invite;
        });
    }

    public function revoke(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $this->debtRepository->show($id);

            $user = $request->user();

            if (! $this->hasPendingRequest($user->id, $id)) {
                throw new InviteNotFoundException();
            }

            $input = ["status" => "revoked"];

            $invite = $this->update($user->id, $id, "pending", $input);
            $this->activityRepo->storeActivity($id, $user->id, 'debt', ['type' => 'invited_revoked', 'userId' => $user->id]);

            return $invite;
        });
    }

    public function getInvitationsStats(Request $request)
    {
        $user = $request->user();

        return [
            'sentInvites'     => DebtUserInvite::query()->invitedBy($user->id)->count(),
            'receivedInvites' => DebtUserInvite::query()->user($user->id)->count(),
            'pendingInvites'  => DebtUserInvite::query()->invitedBy($user->id)->status('pending')->count(),
            'awaitingInvites' => DebtUserInvite::query()->user($user->id)->status('pending')->count(),
        ];
    }

    // Private Methods
    private function isSelf(string $receiverId, string $userId)
    {
        return $receiverId == $userId;
    }
    private function exceededDeclines(string $receiverId, string $debtId, int $maxDeclines, int $days)
    {
        $limitDate = Helpers::getOldDate($days);

        return DebtUserInvite::query()->user($receiverId)->debt($debtId)->status('revoked')->where('created_at', '>=', $limitDate)->count() >= $maxDeclines;
    }
    private function hasPendingRequest(string $receiverId, string $debtId)
    {
        return DebtUserInvite::query()->user($receiverId)->debt($debtId)->status('pending')->exists();
    }
    private function destroy(string $userId, string $debtId, string $status)
    {
        $invite = DebtUserInvite::query()->user($userId)->debt($debtId)->status($status)->first();

        $invite->delete();

        return $invite;
    }
    private function update(string $userId, string $debtId, string $status, array $input)
    {
        $invite = DebtUserInvite::query()->user($userId)->debt($debtId)->status($status)->first();

        $invite->update($input);

        return $invite;
    }
}
