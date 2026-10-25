<?php

namespace Modules\RecurringTransaction\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class StoreRecurringTransactionRequest extends RecurringTransactionRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:categories,id',
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:expense,revenue',
            'amount' => 'required|numeric|min:0.01',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly,yearly,custom',
            'custom_frequency_days' => 'required_if:frequency,custom|nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_linked' => 'boolean',
            'linked_type' => 'required_if:is_linked,true|nullable|in:debt,financial_goal',
            'linked_id' => 'required_if:is_linked,true|nullable|integer',
        ];
    }
}
