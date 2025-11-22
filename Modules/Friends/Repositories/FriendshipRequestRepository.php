<?php

namespace Modules\Friends\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Friends\Core\Helpers;
use Modules\Friends\Entities\FriendshipRequestModel;
use Modules\Friends\Exceptions\AlreadyFriendsException;
use Modules\Friends\Exceptions\FriendRequestNotFoundException;
use Modules\Friends\Exceptions\SelfFriendshipException;
use Modules\Friends\Exceptions\UserBlockedException;

class FriendshipRequestRepository
{
    protected FriendshipRepository $friendshipRepository;

    public function __construct(FriendshipRepository $friendshipRepository)
    {
        $this->friendshipRepository = $friendshipRepository;
    }

    public function send(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            if ($this->isSelf($id, $user->id)) throw new SelfFriendshipException();
            if ($this->areFriends($id, $user->id)) throw new AlreadyFriendsException();
            if ($this->hasPendingRequest($id, $user->id)) throw new \Modules\Friends\Exceptions\FriendRequestAlreadySentException();
            if ($this->isBlocked($id, $user->id)) throw new UserBlockedException();
            if ($this->exceededDeclines($id, $user->id, 3, 30,)) throw new \Modules\Friends\Exceptions\FriendRequestLimitExceededException();

            $friendRequest = FriendshipRequestModel::create([
                'sender_id' => $user->id,
                'receiver_id' => $id,
                'status' => 'pending'
            ]);

            return $friendRequest;
        });
    }

    public function accept(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            if ($this->isSelf($id, $user->id)) throw new SelfFriendshipException();
            if ($this->areFriends($id, $user->id)) throw new AlreadyFriendsException();
            if (!$this->hasPendingRequest($id, $user->id)) throw new FriendRequestNotFoundException();
            if ($this->isBlocked($id, $user->id)) throw new UserBlockedException();

            $friendRequest = $this->getRequest($user->id, $id);

            if ($friendRequest->sender_id == $user->id) throw new \Modules\Friends\Exceptions\SenderCannotAcceptFriendRequestException();

            $friendship = $this->friendshipRepository->makeRelation($friendRequest->sender_id, $friendRequest->receiver_id, 'friend');
            $friendRequest->delete();

            return $friendship;
        });
    }

    public function decline(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            if ($this->isSelf($id, $user->id)) throw new SelfFriendshipException();
            if (!$this->hasPendingRequest($id, $user->id)) throw new FriendRequestNotFoundException();
            if ($this->isBlocked($id, $user->id)) throw new UserBlockedException();

            $friendRequest = FriendshipRequestModel::query()->sender($id)->receiver($user->id)->status('pending')->first();

            $friendRequest->update(['status' => 'declined']);

            return $friendRequest;
        });
    }

    // private methods
    private function getRequest(string $userIdO, string $userIdT)
    {
        return FriendshipRequestModel::query()
            ->where(function ($query) use ($userIdT, $userIdO) {
                $query->where(fn($q) => $q->sender($userIdT)->receiver($userIdO))
                    ->orWhere(fn($q) => $q->sender($userIdO)->receiver($userIdT));
            })
            ->status('pending')
            ->first();
    }
    private function isSelf(string $receiverId, string $userId)
    {
        return $receiverId == $userId;
    }
    private function areFriends(string $receiverId, string $userId)
    {
        return $this->friendshipRepository->areFriends($receiverId, $userId);
    }
    private function hasPendingRequest(string $receiverId, string $userId)
    {
        return FriendshipRequestModel::query()->sender($receiverId)->receiver($userId)->status('pending')->exists() || FriendshipRequestModel::query()->sender($userId)->receiver($receiverId)->status('pending')->exists();
    }
    private function isBlocked(string $receiverId, string $userId)
    {
        return $this->friendshipRepository->isBlocked($receiverId, $userId);
    }
    private function exceededDeclines(string $receiverId, string $userId, int $maxDeclines, int $days)
    {
        $limitDate = Helpers::getOldDate($days);

        return FriendshipRequestModel::query()->sender($userId)->receiver($receiverId)->status('decline')->where('created_at', '>=', $limitDate)->count() >= $maxDeclines;
    }
}
