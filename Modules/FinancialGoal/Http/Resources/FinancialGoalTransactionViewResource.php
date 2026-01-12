<?php
namespace Modules\FinancialGoal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;

class FinancialGoalTransactionViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
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
            'currencySymbol'    => $this->currencySymbol,
            'currencyId'        => $this->currencyId,
            'accountName'       => $this->accountName,
        ];
    }
}
