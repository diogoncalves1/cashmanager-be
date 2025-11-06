<?php

namespace Modules\Permission\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
            "name" => "required|string|max:255",
        ];

        if ($this->get("role_id"))
            $rules['code'] = ['required', Rule::unique('roles')->ignore($this->get("role_id")), 'max:191'];
        else
            $rules['code'] = "required|unique:roles|max:191";

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
