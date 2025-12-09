<?php
namespace Modules\Currency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Language\Repositories\LanguageRepository;

class CurrencyRequest extends FormRequest
{
    protected LanguageRepository $languageRepository;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

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
        $rules = [
            'symbol' => 'required|string|max:50',
        ];

        $languages = $this->languageRepository->allCodes();

        foreach ($languages as $language) {
            $rules[$language] = "required|string|max:191";
        }

        if ($this->get('currency_id')) {
            $rules['code'] = ['required', 'string', Rule::unique('currencies', 'code')->ignore($this->get('currency_id')), 'size:3'];
        } else {
            $rules['code'] = 'required|string|unique:currencies,code|size:3';
        }

        return $rules;
    }
}
