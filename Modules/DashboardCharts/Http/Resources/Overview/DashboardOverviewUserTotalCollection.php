<?php
namespace Modules\DashboardCharts\Http\Resources\Overview;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DashboardOverviewUserTotalCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
        return DashboardOverviewUserTotalResource::collection($this->collection);
    }
}
