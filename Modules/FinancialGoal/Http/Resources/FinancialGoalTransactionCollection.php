<?php
namespace Modules\FinancialGoal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FinancialGoalTransactionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return FinancialGoalTransactionResource::collection($this->collection);
    }
}
