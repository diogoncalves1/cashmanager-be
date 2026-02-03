<?php
namespace Modules\FinancialGoal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFinancialGoalContributionViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'contrubuttions' => '',
            'sharedRole'     => $this->whenHas('sharedRole', new \Modules\SharedRoles\Http\Resources\SharedRoleResource($this->sharedRole)),
        ];
    }
}
