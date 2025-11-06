<?php

namespace Modules\SharedRoles\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SharedRoleCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return SharedRoleResource::collection($this->collection);
    }
}
