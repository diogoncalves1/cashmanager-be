<?php
namespace Modules\Debts\Http\Resources\DebtPayments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DebtPaymentViewCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
