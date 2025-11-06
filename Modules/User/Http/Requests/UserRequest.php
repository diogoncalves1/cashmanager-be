<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
            'roles' => 'nullable|array'
        ];

        if ($this->get('user_id')) {
            $rules['email'] = ['required', 'email', Rule::unique('users')->ignore($this->get('user_id')), 'max:191'];
            $rules['password'] = 'nullable|min:8|max:191';
        } else {
            $rules['email'] = 'required|email|unique:users|max:191';
            $rules['password'] = 'required|min:8|max:191';
        }

        return $rules;
    }
}
