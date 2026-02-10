<?php
namespace Modules\Language\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Language\Repositories\LanguageRepository;

class LanguageRequest extends FormRequest
{
    protected LanguageRepository $languageRepo;

    public function __construct(LanguageRepository $languageRepo)
    {
        $this->languageRepo = $languageRepo;
    }
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {

        if ($this->has('language_id')) {
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('languages', 'code')->ignore($this->get('language_id'))];
        } else {
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('languages', 'code')];
        }

        $languages = $this->languageRepo->allCodes();

        if (count($languages)) {
            $rules = [
                'name' => 'required|array',
            ];

            foreach ($languages as $language) {
                $rules['name.' . $language] = "required|string|max:100";
                $rules['name.' . $language] = "required|string|max:100";
            }
        } else {
            $rules = [
                'name' => 'nullable|array',
            ];
        }

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
