<?php
namespace Modules\Notification\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Language\Repositories\LanguageRepository;

class BroadcastNotificationRequest extends FormRequest
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
            'send_email' => 'nullable|boolean',
            'title'      => 'required|array',
            'message'    => 'required|array',
            'pathname'   => 'nullable|string',
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

        if ($this->get('notification_id')) {
            $rules['code'] = ['required', 'string', Rule::unique('notification_types', 'code')->ignore($this->get('notification_type_code'))];
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

    /**
     * Mensagens de erro personalizadas.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'code.required'             => 'O campo código é obrigatório.',
            'code.unique'               => 'O código já está em uso.',
            'title.*.required'          => 'O título é obrigatório.',
            'message.*.required'        => 'A mensagem é obrigatória.',
            'mail_subject.*.required'   => 'Ao marcar como "enviar email" o assunto no email é obrigatório.',
            'mail_message.*.required'   => 'Ao marcar como "enviar email" a mensagem no email é obrigatório.',
            'mail_signature.*.required' => 'Ao marcar como "enviar email" a assinatura no email é obrigatório.',
        ];
    }
}
