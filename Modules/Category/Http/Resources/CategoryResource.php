<?php
namespace Modules\Category\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user() ? $request->user : Auth::user();

        $lang = $user?->preferences?->lang ?? 'en';

        return [
            'id'             => $this->id,
            'name'           => $this->is_default ? $this->name->{$lang} : $this->name,
            'typeTranslated' => __('category::attributes.categories.type.' . $this->type),
            'type'           => $this->type,
            'icon'           => $this->icon,
            'color'          => $this->color,
            'parent'         => $this->whenNotNull($this->parent ? new CategoryResource($this->parent) : null),
        ];
    }
}
