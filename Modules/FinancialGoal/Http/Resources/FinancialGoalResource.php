<?php
namespace Modules\FinancialGoal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialGoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'currencyId'        => $this->currency_id,
            'name'              => $this->name,
            'totalAmount'       => (float) $this->total_amount,
            'contributedAmount' => (float) $this->contributed_amount,
            'startDate'         => $this->start_date,
            'dueDate'           => $this->due_date,
            'status'            => $this->status,
            'description'       => $this->description,
            'completedAt'       => $this->whenNotNull($this->completed_at),
            'priority'          => $this->priority,
            'canceledAt'        => $this->whenNotNull($this->canceled_at),
            'createdAt'         => $this->created_at,
        ];
    }
}
