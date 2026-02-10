<?php
namespace Modules\FinancialGoal\Http\Resources\FinancialGoalUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FinancialGoalUserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return FinancialGoalUserResource::collection($this->collection);
    }
}
