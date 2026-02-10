<?php
namespace Modules\UserPreferences\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Http\Resources\CurrencyResource;

class UserPreferecesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'lang'        => $this->lang,
            'currency_id' => (string) $this->currency_id,
            'currency'    => new CurrencyResource($this->currency),
        ];
    }
}
