<?php
namespace Modules\Accounts\Http\Resources\Charts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;

class BalanceChartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'date'              => $this->date,
            'amount'            => $this->amount,
            'transactionAmount' => (float) $this->transactionAmount,
            'amountFormated'    => Helpers::formatMoneyWithCurrency($this->amount, $this->currencyCode, $this->currencySymbol),
        ];
    }
}
