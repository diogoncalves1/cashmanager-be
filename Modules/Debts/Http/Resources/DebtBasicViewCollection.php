<?php
namespace Modules\Debts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DebtBasicViewCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return DebtBasicViewResource::collection($this->collection);
    }
}
