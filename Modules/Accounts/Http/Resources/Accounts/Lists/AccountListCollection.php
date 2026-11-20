<?php

namespace Modules\Accounts\Http\Resources\Accounts\Lists;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AccountListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return AccountListResource::collection($this->collection);
    }
}
