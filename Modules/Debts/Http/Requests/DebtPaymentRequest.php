<?php
namespace Modules\Debts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DebtPaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            "debt_id"       => "required|exists:debts,id",
            "status"        => "required|in:completed,pending",
            "amount"        => "required|numeric|min:0",
            "date"          => "required|string",
            "interest_rate" => "nullable|numeric|min:0",
            "description"   => "nullable|string",
            "account_id"    => "required|exists:accounts,id",
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
