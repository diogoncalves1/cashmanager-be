<?php
namespace Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'roles'      => $this->whenLoaded('roles'),
            'sharedRole' => $this->whenHas('sharedRole', new \Modules\SharedRoles\Http\Resources\SharedRoleResource($this->sharedRole)),
        ];
    }
}
