<?php
namespace Modules\RecurringTransaction\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RecurringTransactionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return RecurringTransactionResource::collection($this->collection);
    }
}
