<?php
namespace Modules\Notification\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationKeywordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'description' => 'nullable|string|max:255',
        ];

        if ($this->get('keyword_id')) {
            $rules['keyword'] = ['required', 'string', Rule::unique('notification_keywords', 'keyword')->ignore($this->get('keyword_id'))];
        } else {
            $rules['keyword'] = 'required|string|unique:notification_keywords,keyword';
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
