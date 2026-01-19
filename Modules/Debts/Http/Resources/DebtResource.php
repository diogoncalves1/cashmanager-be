<?php
namespace Modules\Debts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'currencyId'    => $this->currency_id,
            'name'          => $this->name,
            'totalAmount'   => $this->total_amount,
            'paidAmount'    => $this->paid_amount,
            'months'        => $this->months,
            'insterestRate' => $this->interest_rate,
            'startDate'     => $this->start_date,
            'dueDate'       => $this->due_date,
            'description'   => $this->description,
            'isRecurring'   => $this->is_recurring,
            'type'          => $this->type,
            'status'        => $this->status,
            'paidAt'        => $this->whenNotNull($this->paid_at),
        ];
    }
}
