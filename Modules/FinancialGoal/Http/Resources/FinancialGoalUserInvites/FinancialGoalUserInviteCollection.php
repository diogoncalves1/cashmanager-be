<?php
namespace Modules\FinancialGoal\Http\Resources\FinancialGoalUserInvites;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FinancialGoalUserInviteCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return FinancialGoalUserInviteResource::collection($this->collection);
    }
}
