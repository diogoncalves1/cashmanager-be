<?php
namespace Modules\Notification\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Language\Repositories\LanguageRepository;

class NotificationTypeRequest extends FormRequest
{
    public function __construct(protected LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title'      => 'required|array',
            'message'    => 'required|array',
            'pathname'   => 'nullable|string',
            'keywords'   => 'nullable|array',
            'keywords.*' => 'required|exists:notification_keywords,id',
        ];

        if ($this->get('send_email')) {
            $rules['mail_subject']   = 'required|array';
            $rules['mail_message']   = 'required|array';
            $rules['mail_signature'] = 'required|array';
        } else {
            $rules['mail_subject']   = 'nullable|array';
            $rules['mail_message']   = 'nullable|array';
            $rules['mail_signature'] = 'nullable|array';
        }

        if ($this->get('type_id')) {
            $rules['code'] = ['required', 'string', Rule::unique('notification_types', 'code')->ignore($this->get('type_id'))];
        } else {
            $rules['code'] = 'required|string|unique:notification_types,code';
        }

        $languages = $this->languageRepository->allCodes();

        foreach ($languages as $language) {
            $rules['title.' . $language]   = "required|string";
            $rules['message.' . $language] = "required|string";
            if ($this->get('send_email')) {
                $rules['mail_subject.' . $language]   = 'required|string';
                $rules['mail_message.' . $language]   = 'required|string';
                $rules['mail_signature.' . $language] = 'required|string';
            }
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
