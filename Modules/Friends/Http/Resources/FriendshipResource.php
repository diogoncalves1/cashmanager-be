<?php
namespace Modules\Friends\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Http\Resources\UserShareResource;

class FriendshipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        ($this->type == 'friend') ? ['remove' => true, 'block' => true] : ['unblock' => true];

        return [
            'id'         => $this->id,
            'status'     => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user'       => new UserShareResource($this->sender_id === $request->user()->id ? $this->receiver : $this->sender),
        ];
    }
}
