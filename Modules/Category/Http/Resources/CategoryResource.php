<?php

namespace Modules\Category\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $owned = ! $this->is_default && (string) $this->user_id === (string) $request->user()?->id;
        $usageKnown = array_key_exists('transactions_exists', $this->resource->getAttributes());

        return [
            'id' => (string) $this->id,
            'name' => $this->resource->localizedName(app()->getLocale()),
            'typeTranslated' => __('category::attributes.categories.type.'.$this->type),
            'type' => $this->type,
            'icon' => $this->icon,
            'color' => $this->color,
            'isDefault' => (bool) $this->is_default,
            'isArchived' => $this->archived_at !== null,
            'canManage' => $owned,
            'canDelete' => $owned && $usageKnown && ! $this->transactions_exists && ! $this->recurrings_exists,
        ];
    }
}
