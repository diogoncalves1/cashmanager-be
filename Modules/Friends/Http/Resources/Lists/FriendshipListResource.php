<?php

namespace Modules\Friends\Http\Resources\Lists;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Http\Resources\UserShareResource;

class FriendshipListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        $user = $request->user();
        $data = $row->attributesToArray();
        $data['user'] = $this->user($row, $request);
        $data['actions'] = $this->actions($row, $request);
        foreach (['sender_id'] as $field) {
            unset($data[$field]);
        }
        foreach (['receiver_id'] as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    private function user($row, Request $request): mixed
    {
        $user = $request->user();

        return ($row->sender_id == $user->id) ? new UserShareResource($row->receiver) : new UserShareResource($row->sender);

    }

    private function actions($row, Request $request): mixed
    {
        $user = $request->user();

        return ($row->status == 'friend') ? ['remove' => true, 'block' => true] : ['unblock' => true];

    }
}
