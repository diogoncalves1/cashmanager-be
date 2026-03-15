<?php
namespace Modules\ActivityLog\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivityLogCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request)
    {
        return ActivityLogCollection::collection($this->collection);
    }
}
