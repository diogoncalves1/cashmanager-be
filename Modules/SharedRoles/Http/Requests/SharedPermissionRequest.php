<?php

namespace Modules\SharedRoles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SharedPermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255'
        ];

        if ($this->get("shared_permission_id"))
            $rules['code'] = ['required', Rule::unique('shared_permissions')->ignore($this->get("shared_permission_id")), 'max:191'];
        else
            $rules['code'] = "required|unique:shared_permissions|max:191";

        return $rules;
    }
}
