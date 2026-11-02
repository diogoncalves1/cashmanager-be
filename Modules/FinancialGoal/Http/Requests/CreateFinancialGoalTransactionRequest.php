<?php
namespace Modules\FinancialGoal\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateFinancialGoalTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'financial_goal_id' => 'required|integer|exists:financial_goals,id',
            'amount'            => 'required|numeric|min:0.01|max:999999999999.99|decimal:0,2',
            'date'              => 'required|date',
            'status'            => 'required|string|in:completed,pending',
            'type'              => 'required|string|in:withdrawal,contribution,usage',
            'description'       => 'nullable|string|max:255',
            'transaction_id' => 'nullable|integer|exists:transactions,id',
            'category_id' => 'exclude_unless:type,usage|nullable|integer|exists:categories,id',
            'request_key' => [\Illuminate\Validation\Rule::requiredIf(fn () =>
                is_scalar($this->input('financial_goal_id'))
                && \Illuminate\Support\Facades\Schema::hasColumn('financial_goals', 'funding_mode')
                && \Modules\FinancialGoal\Entities\FinancialGoal::whereKey($this->input('financial_goal_id'))->where('funding_mode', 'reservation')->exists()
            ), 'nullable', 'string', 'max:100'],
            'recurring_transaction_id' => 'prohibited',
            'is_recurring' => 'prohibited',
            'account_id'        => 'required|integer|exists:accounts,id',
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
