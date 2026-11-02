<?php
namespace Modules\FinancialGoal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialGoalTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {

        return [
            'id'              => $this->id,
            'financialGoalId' => $this->financial_goal_id,
            'userId'          => $this->user_id,
            'transactionId'   => $this->transaction_id,
            'type'            => $this->type,
            'amount'          => $this->amount,
            'date'            => $this->date,
            'description'     => $this->description,
            'status'          => $this->status,
        ];
    }
}
