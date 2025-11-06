<?php

namespace Modules\Permission\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
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
            "category" => "required|string|max:255",
            "visible" => 'nullable'
        ];

        if ($this->get("permission_id"))
            $rules["code"] = ['required', Rule::unique('permissions')->ignore($this->get("permission_id")), 'max:255'];
        else
            $rules["code"] = "required|unique:permissions|max:255";

        return $rules;
    }

    public function messages(): array
    {
        return [
            'code.required' => 'O código é obrigatório.',
            'code.unique'   => 'Este código já existe.',
        ];
    }
}
