<?php
namespace Modules\Accounts\Http\Resources\Stats;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;
use Modules\Debts\Core\Helpers as CoreHelpers;

class AccountIndividualDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'monthlyRevenues'     => Helpers::formatMoneyWithCurrency($this['monthlyRevenues'] ?? 0, $this['currencyCode'] ?? $this->currencyCode, $this['currencySymbol'] ?? $this->currencySymbol, true),
            'monthlyExpenses'     => Helpers::formatMoneyWithCurrency($this['monthlyExpenses'] ?? 0, $this['currencyCode'] ?? $this->currencyCode, $this['currencySymbol'] ?? $this->currencySymbol, true),
            'revenuesVsLastMonth' => CoreHelpers::percentage($this['lastMonthRevenues'] ?? 0, $this['monthlyRevenues'] ?? 0),
            'expensesVsLastMonth' => CoreHelpers::percentage($this['lastMonthExpenses'] ?? 0, $this['monthlyExpenses'] ?? 0),
            'balanceVsLastMonth'  => CoreHelpers::percentage($this['balanceLastMonth'] ?? 0, $this['balance'] ?? 0),
            'lastMonthRevenue'    => Helpers::formatMoneyWithCurrency($this['lastMonthRevenues'] ?? 0, $this['currencyCode'] ?? $this->currencyCode, $this['currencySymbol'] ?? $this->currencySymbol, true),
            'lastMonthExpenses'   => Helpers::formatMoneyWithCurrency($this['lastMonthExpenses'] ?? 0, $this['currencyCode'] ?? $this->currencyCode, $this['currencySymbol'] ?? $this->currencySymbol, true),
        ];
    }
}
