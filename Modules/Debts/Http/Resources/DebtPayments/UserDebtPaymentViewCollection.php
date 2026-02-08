<?php
namespace Modules\Debts\Http\Resources\DebtPayments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserDebtPaymentViewCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return UserDebtPaymentViewResource::collection($this->collection);
    }
}
