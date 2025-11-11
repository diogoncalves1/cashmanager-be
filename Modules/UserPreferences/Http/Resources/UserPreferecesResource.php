<?php

namespace Modules\UserPreferences\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferecesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'lang' => $this->lang,
            'currency_id' => $this->currency_id,
            'currency' => $this->currency,
            'user' => $this->user,
        ];
    }
}
