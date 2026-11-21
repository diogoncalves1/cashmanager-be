<?php

namespace Modules\Accounts\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $presence = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'name' => "$presence|string|max:255",
            'currency_id' => "$presence|exists:currencies,id",
            'type' => "$presence|string|in:cash,bank_account,credit_card,digital_wallet",
            'active' => "$presence",
        ];
    }
}
