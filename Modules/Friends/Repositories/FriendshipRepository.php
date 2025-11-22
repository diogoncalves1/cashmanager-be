<?php

namespace Modules\Friends\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Friends\Entities\Friendship;
use Modules\Friends\Exceptions\FriendshipNotFoundException;
use Modules\Friends\Exceptions\SelfBlockNotAllowedException;
use Modules\Friends\Exceptions\UserBlockedException;

class FriendshipRepository
{
    public function remove(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            if (!$this->hasRelation($id, $user->id)) throw new FriendshipNotFoundException();
            if ($this->isBlocked($id, $user->id)) throw new UserBlockedException();

            $friendship = Friendship::query()
                ->where(function ($query) use ($id, $user) {
                    $query->where(fn($q) => $q->sender($id)->receiver($user->id))
                        ->orWhere(fn($q) => $q->sender($user->id)->receiver($id));
                })
                ->status('friend')
                ->first();

            $friendship->delete();

            return $friendship;
        });
    }

    public function block(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            if ($this->isSelf($id, $user->id)) throw new SelfBlockNotAllowedException();
            if ($this->isBlocked($id, $user->id)) throw new UserBlockedException();

            if ($this->hasRelation($id, $user->id))
                Friendship::query()
                    ->where(function ($query) use ($id, $user) {
                        $query->where(fn($q) => $q->sender($id)->receiver($user->id))
                            ->orWhere(fn($q) => $q->sender($user->id)->receiver($id));
                    })->delete();

            DB::table('friendship_requests')->where(function ($query) use ($id, $user) {
                $query->where(fn($q) => $q->where('sender_id', $id)->where('receiver_id', $user->id))
                    ->orWhere(fn($q) => $q->where('sender_id', $user->id)->where('receiver_id', $id));
            })->delete();

            $friendship = $this->makeRelation($user->id, $id, 'blocked');

            return $friendship;
        });
    }

    public function unblock(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            if ($this->isSelf($id, $user->id)) throw new SelfBlockNotAllowedException();
            if (!$this->isBlocked($id, $user->id)) throw new \Modules\Friends\Exceptions\UserNotBlockedException();

            $friendship = Friendship::status("blocked")->sender($user->id)->receiver($id)->first();

            $friendship->delete();

            return $friendship;
        });
    }

    // Private Methods
    public function makeRelation(string $senderId, string $receiverId, string $status)
    {
        return DB::transaction(function () use ($senderId, $receiverId, $status) {
            return Friendship::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'status' => $status
            ]);
        });
    }
    public function isSelf(string $receiverId, string $userId)
    {
        return $receiverId == $userId;
    }
    public function areFriends(string $receiverId, string $userId)
    {
        return Friendship::query()->sender($receiverId)->receiver($userId)->status('friend')->exists() || Friendship::query()->sender($userId)->receiver($receiverId)->status('friend')->exists();
    }
    public function isBlocked(string $receiverId, string $userId)
    {
        return Friendship::query()->sender($userId)->receiver($receiverId)->status('blocked')->exists() || Friendship::query()->sender($receiverId)->receiver($userId)->status('blocked')->exists();
    }
    public function hasRelation(string $userSenderId, string $userId)
    {
        return Friendship::query()->sender($userSenderId)->receiver($userId)->exists() || Friendship::query()->sender($userId)->receiver($userSenderId)->exists();
    }
}
