<?php
namespace Modules\FinancialGoal\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FinancialGoalRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0.01',
            'currency_id'  => 'required|exists:currencies,id',
            'start_date'   => 'required|date',
            'due_date'     => 'required|date|after_or_equal:start_date',
            'description'  => 'nullable|string',
            'priority'     => 'nullable|string|in:low,medium,high',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
