<?php

namespace Modules\RecurringTransaction\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class UpdateRecurringTransactionRequest extends RecurringTransactionRequest
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
            'amount' => 'sometimes|numeric|min:0.01',
            'description' => 'nullable|string',
            'end_date' => 'nullable|date',
            'custom_frequency_days' => 'nullable|integer|min:1',
            'title' => 'sometimes|string|max:255',
        ];
    }
}
