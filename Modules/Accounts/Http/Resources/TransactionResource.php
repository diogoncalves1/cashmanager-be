<?php
namespace Modules\Accounts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'accountId'   => (int) $this->account_id,
            'type'        => $this->type,
            'amount'      => (float) $this->amount,
            'date'        => $this->date,
            'description' => $this->description,
            'status'      => $this->status,
            'categoryId'  => (int) $this->category_id,
            'userId'      => (int) $this->user_id,
        ];
    }
}
