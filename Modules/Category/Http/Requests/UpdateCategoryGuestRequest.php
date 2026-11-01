<?php

namespace Modules\Category\Http\Requests;

class UpdateCategoryGuestRequest extends CategoryGuestRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'type' => 'prohibited'];
    }
}
