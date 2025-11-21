<?php
namespace Modules\Accounts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'currencyId' => $this->currency_id,
            'type'       => $this->type,
            'balance'    => (float) $this->balance,
            'status'     => $this->status,
        ];
    }
}
