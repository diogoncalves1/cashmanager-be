<?php
namespace Modules\Debts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DebtPaymentUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            "amount"        => "required|numeric|min:0",
            "date"          => "required|string",
            "interest_rate" => "required|numeric|min:0",
            "description"   => "nullable|string",
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
