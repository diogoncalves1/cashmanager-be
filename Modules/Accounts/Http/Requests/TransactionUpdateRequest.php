<?php
namespace Modules\Accounts\Http\Requests;

use Modules\Category\Http\Requests\ValidatesCategorySelection;

use Illuminate\Foundation\Http\FormRequest;

class TransactionUpdateRequest extends FormRequest
{
    use ValidatesCategorySelection;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount'      => 'required|numeric|min:0',
            'date'        => 'required|date',
            'description' => 'nullable|string',
            'category_id' => 'required|integer|exists:categories,id',
        ];
    }
}
