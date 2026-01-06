<?php
namespace Modules\Accounts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;
use Modules\User\Http\Resources\UserShareCollection;

class AccountViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $typeTranslated               = __('accounts::attributes.accounts.type.' . $this->type);
        $balanceFormated              = Helpers::formatMoneyWithSymbolAndCurrency($this->balance, $this->currencyCode, $this->currencySymbol);
        $balanceFormatedWithoutSymbol = Helpers::formatMoneyWithCurrency($this->balance, $this->currencyCode, $this->currencySymbol);
        $statusTranslated             = __("accounts::attributes.accounts.status." . ($this->status ? 'active' : 'disabled'));

        $user = $request->user();

        $account    = $this->account;
        $sharedRole = $account->userSharedRole($account, $user->id);

        $actions = ['view' => $sharedRole?->hasPermission("viewAccount"), 'edit' => $sharedRole?->hasPermission("editAccount"), 'destroy' => $sharedRole?->hasPermission("destroyAccount"), 'manage' => $sharedRole?->hasPermission("manageAccountUsers"), 'addTransaction' => $sharedRole?->hasPermission("createTransaction")];

        foreach ($this->users as &$user) {
            $user->sharedRole = $user->pivot->sharedRole;
        }
        return [
            'id'                           => $this->id,
            'name'                         => $this->name,
            'currencySymbol'               => $this->currencySymbol,
            'currencyCode'                 => $this->currencyCode,
            'currencyId'                   => $this->currencyId,
            'type'                         => $this->type,
            'typeTranslated'               => $typeTranslated,
            'balance'                      => (float) $this->balance,
            'balanceFormated'              => $balanceFormated,
            'balanceFormatedWithoutSymbol' => $balanceFormatedWithoutSymbol,
            'active'                       => (bool) $this->status,
            'statusTranslated'             => $statusTranslated,
            'totalTransactions'            => $this->totalTransactions,
            'totalRevenues'                => Helpers::formatMoneyWithCurrency($this->totalRevenues, $this->currencyCode, $this->currencySymbol, true),
            'totalExpenses'                => Helpers::formatMoneyWithCurrency($this->totalExpenses, $this->currencyCode, $this->currencySymbol, true),
            'createdAt'                    => $this->createdAt,
            'users'                        => new UserShareCollection($this->users),
            'actions'                      => $actions,
        ];
    }
}
