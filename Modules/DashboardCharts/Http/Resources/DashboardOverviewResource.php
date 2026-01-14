<?php
namespace Modules\DashboardCharts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Http\Resources\CurrencyResource;
use Modules\DashboardCharts\Http\Resources\Overview\DashboardOverviewAnnualyCollection;
use Modules\DashboardCharts\Http\Resources\Overview\DashboardOverviewMonthlyCollection;
use Modules\DashboardCharts\Http\Resources\Overview\DashboardOverviewQuarterlyCollection;
use Modules\DashboardCharts\Http\Resources\Overview\DashboardOverviewUserTotalCollection;

class DashboardOverviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'charts'    => [
                'monthly'   => new DashboardOverviewMonthlyCollection($this['charts']['monthly']),
                'quarterly' => new DashboardOverviewQuarterlyCollection($this['charts']['quarterly']),
                'annualy'   => new DashboardOverviewAnnualyCollection($this['charts']['annualy']),
                'userTotal' => new DashboardOverviewUserTotalCollection($this['charts']['userTotal']),
            ],
            'dashboard' => [
                'revenueClasses'     => $this['dashboard']['revenueClasses'],
                'totalRevenues'      => $this['dashboard']['totalRevenues'],
                'totalUser'          => $this['dashboard']['totalUser'],
                'revenuePercentage'  => $this['dashboard']['revenuePercentage'],
                'expensesClasses'    => $this['dashboard']['expensesClasses'],
                'expensesPercentage' => $this['dashboard']['expensesPercentage'],
                'totalExpenses'      => $this['dashboard']['totalExpenses'],
                'totalClasses'       => $this['dashboard']['totalClasses'],
                'totalPercentage'    => $this['dashboard']['totalPercentage'],
                'currency'           => new CurrencyResource($this['dashboard']['currency']),
            ],
        ];
    }
}
