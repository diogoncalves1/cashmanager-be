<?php

namespace Modules\User\Http\Resources\Lists;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Friends\Repositories\FriendshipRepository;
use Modules\Friends\Repositories\FriendshipRequestRepository;

class SearchUserListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        $user = $request->user();
        $data = $row->only(['id', 'name', 'username']);
        $data['status'] = $this->status($row, $request);

        foreach (['password'] as $field) {
            unset($data[$field]);
        }
        foreach (['username_updated_at'] as $field) {
            unset($data[$field]);
        }
        foreach (['email'] as $field) {
            unset($data[$field]);
        }
        foreach (['created_at'] as $field) {
            unset($data[$field]);
        }
        foreach (['updated_at'] as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    private function status($row, Request $request): mixed
    {
        $user = $request->user();

        if (app(FriendshipRepository::class)->isBlocked($row->id, $user->id)) {
            return 'blocked';
        }
        if (app(FriendshipRepository::class)->areFriends($row->id, $user->id)) {
            return 'friend';
        }
        if (app(FriendshipRequestRepository::class)->hasPendingRequest($row->id, $user->id)) {
            return 'pending';
        }

        return '';

    }
}
