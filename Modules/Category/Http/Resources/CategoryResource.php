<?php

namespace Modules\Category\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $userLang = app()->getLocale();

        return [
            'name' => $this->default ? $this->name->{$userLang} : $this->name,
            'type' => __('category::attributes.categories.type.' . $this->type),
            'icon' => $this->icon,
            'color' => $this->color,
            'parent' => $this->parent
        ];
    }
}
