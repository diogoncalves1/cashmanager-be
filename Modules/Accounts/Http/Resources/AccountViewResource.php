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

        $user       = $request->user();
        $sharedRole = $this->account->userSharedRole($this->account, $user->id);

        $canEdit               = $sharedRole?->hasPermission("editAccount");
        $canDestroy            = $sharedRole?->hasPermission("destroyAccount");
        $canManage             = $sharedRole?->hasPermission("manageAccountUsers");
        $canCreateTransactions = $sharedRole?->hasPermission("createTransaction");

        $actions = ['edit' => $canEdit, 'destroy' => $canDestroy, 'manage' => $canManage, 'createTransactions' => $canCreateTransactions];

        foreach ($this->users as &$user) {
            $user->sharedRole = $user->pivot->sharedRole;
        }
        foreach ($this->invites as &$user) {
            $user->sharedRole = $user->pivot->sharedRole;
        }
        return [
            'id'                           => $this->id,
            'name'                         => $this->name,
            'currencySymbol'               => $this->currencySymbol,
            'currencyCode'                 => $this->currencyCode,
            'currencyId'                   => (string) $this->currencyId,
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
            'invites'                      => new UserShareCollection($this->invites),
            'actions'                      => $actions,
        ];
    }
}
