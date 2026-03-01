<?php
namespace Modules\Accounts\Http\Resources\Charts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BalanceChartCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return BalanceChartResource::collection($this->collection);
    }
}
