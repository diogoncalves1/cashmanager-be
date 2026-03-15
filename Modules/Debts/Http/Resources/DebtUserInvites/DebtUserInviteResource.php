<?php
namespace Modules\Debts\Http\Resources\DebtUserInvites;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtUserInviteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
