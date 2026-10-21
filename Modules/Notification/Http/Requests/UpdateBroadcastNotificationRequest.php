<?php
namespace Modules\Notification\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBroadcastNotificationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'          => 'required|array',
            'message'        => 'required|array',
            'mail_subject'   => 'nullable|array',
            'mail_message'   => 'nullable|array',
            'mail_signature' => 'nullable|array',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
