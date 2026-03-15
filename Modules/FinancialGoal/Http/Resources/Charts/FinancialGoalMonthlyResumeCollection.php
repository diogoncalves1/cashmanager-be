<?php
namespace Modules\FinancialGoal\Http\Resources\Charts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FinancialGoalMonthlyResumeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return FinancialGoalMonthlyResumeResource::collection($this->collection);
    }
}
