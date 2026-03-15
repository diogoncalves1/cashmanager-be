<?php
namespace Modules\FinancialGoal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;

class UserFinancialGoalContributionViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->id,
            'name'         => $this->name,
            'contribution' => Helpers::formatMoneyWithSymbolAndCurrency($this->totalContributed, $this->currencyCode, $this->currencySymbol),
            'sharedRole'   => $this->whenHas('sharedRole', new \Modules\SharedRoles\Http\Resources\SharedRoleResource($this->sharedRole)),
        ];
    }
}
