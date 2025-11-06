<?php

namespace Modules\SharedRoles\Http\Requests;

use Modules\SharedRoles\Enums\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SharedRoleRequest extends FormRequest
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
            'name' => 'required|array'
        ];

        if ($this->get("shared_role_id"))
            $rules['code'] = ['required', Rule::unique('shared_roles')->ignore($this->get("shared_role_id")), 'max:191'];
        else
            $rules['code'] = "required|unique:shared_roles|max:191";

        $languages = config('languages');

        foreach ($languages as $language) {
            $rules['name.' . $language] = "required|string|max:100";
            $rules['name.' . $language] = "required|string|max:100";
        }

        return $rules;
    }
}
