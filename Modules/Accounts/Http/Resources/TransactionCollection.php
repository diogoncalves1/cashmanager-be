<?php
namespace Modules\Accounts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return TransactionResource::collection($this->collection);
    }
}
