<?php

namespace Modules\Accounts\Http\Resources\Accounts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Http\Resources\CurrencyResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'type' => $this->type,
            'balance' => (float) $this->balance,
            'active' => $this->active,
        ];
    }
}
