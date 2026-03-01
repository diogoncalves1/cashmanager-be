<?php
namespace Modules\Accounts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;

class MonthlyResumeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'month'                             => $this->month,
            'totalRevenue'                      => (float) $this->totalRevenue,
            'totalRevenueFormated'              => Helpers::formatMoneyWithSymbolAndCurrency($this->totalRevenue, $this->currencyCode, $this->currencySymbol),
            'totalRevenueFormatedWithoutSymbol' => Helpers::formatMoneyWithCurrency($this->totalRevenue, $this->currencyCode, $this->currencySymbol),
            'totalExpense'                      => (float) $this->totalExpense,
            'totalExpenseFormated'              => Helpers::formatMoneyWithSymbolAndCurrency($this->totalExpense, $this->currencyCode, $this->currencySymbol),
            'totalExpenseFormatedWithoutSymbol' => Helpers::formatMoneyWithCurrency($this->totalExpense, $this->currencyCode, $this->currencySymbol),
            'profit'                            => Helpers::formatMoneyWithCurrency($this->totalRevenue - $this->totalExpense, $this->currencyCode, $this->currencySymbol),
            'profitFormated'                    => Helpers::formatMoneyWithSymbolAndCurrency($this->totalRevenue - $this->totalExpense, $this->currencyCode, $this->currencySymbol),
        ];
    }
}
