<?php

namespace Modules\Currency\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $locale = in_array(App::getLocale(), config('languages')) ? App::getLocale() : 'en';

        return [
            'id' => $this->id,
            'name' => $this->name->$locale,
            'symbol' => $this->symbol,
            'rate' => (float) $this->rate,
            'code' => $this->code
        ];
    }
}
