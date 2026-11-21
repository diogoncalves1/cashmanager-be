<?php
namespace Modules\Accounts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountBasicViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $reservationAccount = \Modules\Accounts\Entities\Account::find($this->id);
        return array_merge($reservationAccount ? \Modules\FinancialGoal\Services\GoalReservations::accountSummary($reservationAccount) : [], [
            'id'      => (string) $this->id,
            'name'    => $this->name,
            'currencyCode' => $reservationAccount?->currency?->code,
            'type'    => __('accounts::attributes.accounts.type.' . $this->type),
            'typeOrg' => $this->type,
        ]);
    }
}
