<?php

namespace Modules\RecurringTransaction\Http\Requests;

use Modules\Category\Http\Requests\ValidatesCategorySelection;

use Illuminate\Contracts\Validation\ValidationRule;

class UpdateRecurringTransactionRequest extends RecurringTransactionRequest
{
    use ValidatesCategorySelection;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'nullable|integer|exists:categories,id',
            'amount' => 'sometimes|numeric|min:0.01',
            'description' => 'nullable|string',
            'end_date' => 'nullable|date',
            'custom_frequency_days' => 'nullable|integer|min:1',
            'title' => 'sometimes|string|max:255',
        ];
    }
}
