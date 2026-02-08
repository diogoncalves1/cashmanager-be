<?php
namespace Modules\Debts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;

class DebtBasicViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => (string) $this->id,
            'name'            => $this->name,
            'currencySymbol'  => $this->currencySymbol,
            'target'          => (float) $this->target,
            'targetFormated'  => Helpers::formatMoneyWithSymbolAndCurrency($this->target, $this->currencyCode, $this->currencySymbol),
            'current'         => (float) $this->current_amount,
            'currentFormated' => Helpers::formatMoneyWithSymbolAndCurrency($this->current_amount, $this->currencyCode, $this->currencySymbol),
            'interestRate'    => (float) $this->interestRate,
            'monthlyAmount'   => (float) $this->monthlyAmount,
        ];
    }
}
