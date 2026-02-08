<?php
namespace Modules\Debts\Http\Resources\DebtPayments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;

class UserDebtPaymentViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'paid'       => Helpers::formatMoneyWithSymbolAndCurrency($this->totalPaid ?? 0, $this->currencyCode, $this->currencySymbol),
            'sharedRole' => $this->whenHas('sharedRole', new \Modules\SharedRoles\Http\Resources\SharedRoleResource($this->sharedRole)),
        ];
    }
}
