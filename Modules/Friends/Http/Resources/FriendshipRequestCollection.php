<?php

namespace Modules\Friends\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FriendshipRequestCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return FriendshipRequestResource::collection($this->collection);
    }
}
