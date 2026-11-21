<?php

namespace Modules\Accounts\Http\Resources\Transactions\Lists;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;
use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Http\Resources\Accounts\AccountResource;
use Modules\Category\Http\Resources\CategoryResource;
use Modules\Currency\Http\Resources\CurrencyResource;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\FinancialGoal\Services\GoalReservations;
use Modules\SharedRoles\Core\CheckPermission;
use Modules\User\Http\Resources\UserShareResource;

class TransactionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $sharedRole = $this->account->userSharedRole($this->account, user: $this->account->users->find($request->user()->id));

        $canConfirm = $this->status == 'pending' && $this->date <= date('Y-m-d') && CheckPermission::check($sharedRole, 'confirmTransaction');

        $actions = ['confirm' => $canConfirm,
            ...CheckPermission::checkMultiple($sharedRole, ['view' => 'viewTransaction', 'edit' => 'editTransaction', 'destroy' => 'destroyTransaction'])];

        $goalUsages = GoalReservations::expenseGoals($this->id, $request->user()->id);
        if (Schema::hasTable('financial_goal_transactions') && FinancialGoalTransaction::where('transaction_id', $this->id)->exists()) {
            $actions['edit'] = $actions['destroy'] = $actions['confirm'] = false;
        }

        return [
            'goalUsages' => $goalUsages,
            'hasGoalUsage' => $goalUsages !== [],
            'accountId' => $this->account_id,
            'accountName' => $this->account->name,
            'accountType' => $this->account->type,
            'accountTypeTranslated' => __('accounts::attributes.accounts.type.'.$this->account->type),
            'currencyCode' => $this->account->currency->code,
            'currencySymbol' => $this->account->currency->symbol,
            'amountFormated' => Helpers::formatMoneyWithSymbolAndCurrency($this->amount, $this->account->currency->code, $this->account->currency->symbol),
            'statusTranslated' => __('accounts::attributes.transactions.status.'.$this->status),
            'typeTranslated' => __('accounts::attributes.transactions.type.'.$this->type),
            'id' => $this->id,
            'date' => $this->date,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'description' => $this->description,
            'type' => $this->type,
            'user' => new UserShareResource($this->whenLoaded('user')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'currency' => new CurrencyResource($this->account->currency),
            'account' => new AccountResource($this->whenLoaded('account')),
            'actions' => $actions,
        ];
    }
}
