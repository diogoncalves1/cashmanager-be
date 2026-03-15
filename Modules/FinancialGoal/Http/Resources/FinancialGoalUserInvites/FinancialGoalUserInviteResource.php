<?php
namespace Modules\FinancialGoal\Http\Resources\FinancialGoalUserInvites;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\FinancialGoal\Http\Resources\FinancialGoalResource;
use Modules\SharedRoles\Http\Resources\SharedRoleResource;
use Modules\User\Http\Resources\UserShareResource;

class FinancialGoalUserInviteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'sharedRole'       => new SharedRoleResource($this->sharedRole),
            'subject'          => new FinancialGoalResource($this->financialGoal),
            'receiver'         => new UserShareResource($this->user),
            'sender'           => new UserShareResource($this->sender),
            'status'           => $this->status,
            'statusTranslated' => __('accounts::attributes.account-user-invites.status.' . $this->status),
            'invitedAt'        => $this->created_at,
        ];
    }
}
