<?php
namespace Modules\Debts\Http\Resources\DebtUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
