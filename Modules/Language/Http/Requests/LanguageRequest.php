<?php
namespace Modules\Language\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
        ];

        if ($this->has('language_id')) {
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('languages', 'code')->ignore($this->get('language_id'))];
        } else {
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('languages', 'code')];
        }

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
