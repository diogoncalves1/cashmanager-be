<?php
namespace Modules\Accounts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;

class AccountViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $typeTranslated   = __('accounts::attributes.accounts.type.' . $this->type);
        $balanceFormated  = Helpers::formatMoneyWithSymbolAndCurrency($this->balance, $this->currencyCode, $this->currencySymbol);
        $statusTranslated = __("accounts::attributes.accounts.status." . ($this->status ? 'active' : 'disabled'));

        foreach ($this->users as &$user) {
            $user->sharedRole = $user->pivot->sharedRole;
        }
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'currencySymbol'   => $this->currencySymbol,
            'currencyCode'     => $this->currencyCode,
            'type'             => $this->type,
            'typeTranslated'   => $typeTranslated,
            'balance'          => (float) $this->balance,
            'balanceFormated'  => $balanceFormated,
            'status'           => $this->status,
            'statusTranslated' => $statusTranslated,
            'users'            => new \Modules\User\Http\Resources\UserShareCollection($this->users),
        ];
    }
}
