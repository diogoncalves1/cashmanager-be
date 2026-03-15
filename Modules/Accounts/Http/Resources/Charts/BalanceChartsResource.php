<?php
namespace Modules\Accounts\Http\Resources\Charts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceChartsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'charts' =>
            [
                'weekly'    => new BalanceChartCollection($this['charts']['weekly']),
                'monthly'   => new BalanceChartCollection($this['charts']['monthly']),
                'quarterly' => new BalanceChartCollection($this['charts']['quarterly']),
                'annualy'   => new BalanceChartCollection($this['charts']['annualy']),
            ],
        ];
    }
}
