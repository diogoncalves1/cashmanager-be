<?php
namespace Modules\FinancialGoal\Http\Resources\FinancialGoalUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\FinancialGoal\Http\Resources\FinancialGoalResource;
use Modules\SharedRoles\Http\Resources\SharedRoleResource;
use Modules\User\Http\Resources\UserShareResource;

class FinancialGoalUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'sharedRole'    => new SharedRoleResource($this->sharedRole),
            'financialGoal' => new FinancialGoalResource($this->financialGoal),
            'user'          => new UserShareResource($this->user),
            'status'        => $this->status,
            'createdAt'     => $this->created_at,
        ];
    }
}
