<?php
namespace Modules\FinancialGoal\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\FinancialGoal\Http\Resources\FinancialGoalUserResource;

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
