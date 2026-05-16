<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data'            => $this['data'],
            'recordsTotal'    => $this['recordsTotal'],
            'recordsFiltered' => $this['recordsFiltered'],
            ...$this['meta'] ?? [],
        ];
    }
}
