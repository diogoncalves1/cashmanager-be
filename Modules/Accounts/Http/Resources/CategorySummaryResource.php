<?php
namespace Modules\Accounts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Core\Helpers;

class CategorySummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'category'                   => isset(json_decode($this->category, true)['en']) ? json_decode($this->category)->{$user->preferences->lang} : json_decode($this->category),
            'value'                      => (float) $this->value,
            'color'                      => $this->color,
            'valueFormated'              => Helpers::formatMoneyWithSymbolAndCurrency($this->value, $this->currencyCode, $this->currencySymbol),
            'valueFormatedWithoutSymbol' => Helpers::formatMoneyWithCurrency($this->value, $this->currencyCode, $this->currencySymbol),
        ];
    }
}
