<?php
namespace Modules\Accounts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\SharedRoles\Http\Resources\SharedRoleResource;
use Modules\User\Http\Resources\UserShareResource;

class AccountUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'sharedRole' => new SharedRoleResource($this->sharedRole),
            'account'    => new AccountResource($this->account),
            'user'       => new UserShareResource($this->user),
            'createdAt'  => $this->created_at,
        ];
    }
}
