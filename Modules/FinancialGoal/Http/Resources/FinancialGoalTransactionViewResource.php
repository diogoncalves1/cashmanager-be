<?php
namespace Modules\FinancialGoal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Modules\Accounts\Core\Helpers;

class FinancialGoalTransactionViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user() ? $request->user() : Auth::user();

        $lang = $user->preferences?->lang ?? 'en';

        $sharedRole = $this->financialGoal->userSharedRole($this->financialGoal, $user->id);

        $canEdit               = $sharedRole?->hasPermission("updateFinancialGoalTransaction");
        $canDestroy            = $sharedRole?->hasPermission("destroyFinancialGoalTransaction");
        $canConfirm            = $this->status == 'pending' && $this->date <= date('Y-m-d') && $sharedRole?->hasPermission("confirmScheduledFinancialGoalTransactions");
        $canCreateTransactions = $sharedRole?->hasPermission("storeFinancialGoalTransaction");

        $actions = ['edit' => $canEdit, 'destroy' => $canDestroy, 'confirm' => $canConfirm, 'store' => $canCreateTransactions];

        return [
            'id'                => $this->id,
            'type'              => $this->type,
            'typeTranslated'    => __('financialgoal::attributes.financial-goal-transactions.type.' . $this->type),
            'status'            => $this->status,
            'statusTranslated'  => __('financialgoal::attributes.financial-goal-transactions.status.' . $this->status),
            'amount'            => $this->amount,
            'amountFormated'    => Helpers::formatMoneyWithSymbolAndCurrency($this->amount, $this->currencyCode, $this->currencySymbol),
            'date'              => $this->date,
            'description'       => $this->description,
            'userName'          => $this->userName,
            'financialGoalName' => $this->financialGoalName,
            'financialGoalId'   => $this->financialGoalId,
            'currencySymbol'    => $this->currencySymbol,
            'currencyId'        => $this->currencyId,
            'accountName'       => $this->accountName,
            'accountType'       => __('accounts::attributes.accounts.type.' . $this->accountType),
            'sharedRole'        => $this->sharedRoleName->$lang,
            'balanceAfter'      => Helpers::formatMoneyWithSymbolAndCurrency(($this->balanceBefore ?? 0) + $this->amount, $this->currencyCode, $this->currencySymbol),
            'balanceBefore'     => Helpers::formatMoneyWithSymbolAndCurrency($this->balanceBefore ?? 0, $this->currencyCode, $this->currencySymbol),

            'actions'           => $actions,
        ];
    }
}
