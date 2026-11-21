<?php

namespace Modules\Accounts\Http\Resources\Accounts\Lists;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;
use Modules\Currency\Http\Resources\CurrencyResource;
use Modules\FinancialGoal\Services\GoalReservations;
use Modules\SharedRoles\Core\CheckPermission;
use Modules\User\Http\Resources\UserShareCollection;

class AccountListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $sharedRole = $this->userSharedRole($this, user: $this->users->find($request->user()->id));

        $actions = CheckPermission::checkMultiple($sharedRole, [
            'view' => 'viewAccount',
            'edit' => 'editAccount',
            'destroy' => 'destroyAccount',
            'manage' => 'manageAccountUsers',
        ]);

        $reservation = GoalReservations::accountSummary($this->resource);

        return [
            ...$reservation,
            'currencyCode' => $this->currency->code,
            'currencySymbol' => $this->currency->symbol,
            'typeTranslated' => __('accounts::attributes.accounts.type.'.$this->type),
            'balanceFormated' => Helpers::formatMoneyWithSymbolAndCurrency($this->balance, $this->currency->code, $this->currency->symbol),
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'balance' => (float) $this->balance,
            'active' => (bool) $this->active,
            'totalTransactions' => $this->whenCounted('transactions'),
            'users' => UserShareCollection::make($this->whenLoaded('users')),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'actions' => $actions,
        ];
    }
}
