<?php
namespace Modules\DashboardCharts\Http\Resources\Overview;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardOverviewMonthlyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'month'    => $this->month,
            'revenues' => $this->revenues,
            'expenses' => $this->expenses,
        ];
    }
}
