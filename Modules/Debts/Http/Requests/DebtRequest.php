<?php
namespace Modules\Debts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DebtRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            "name"          => "required|string|max:255",
            "total_amount"  => "required|numeric",
            "installments"  => "nullable|integrer",
            "interest_rate" => "nullable|numeric",
            "start_date"    => "required|date",
            "due_date"      => "required|date",
            "currency_id"   => "required|exists:currencies,id",
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
