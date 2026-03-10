<?php

namespace Modules\EmailLayout\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmailTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required',
            'code' => 'required'
        ];
        $rules['descriptions'] = 'size:' . sizeof($this->get('keywords'));

        if ($this->get('email_type_id')) {
            $array = ['code' => ['required', Rule::unique('email_types')->ignore($this->get('email_type_id')), 'max:191']];
        } else {

            $array = ['code' => ['required', 'unique:email_types', 'max:191']];
        }
        $rules = array_merge($rules, $array);

        return $rules;

    }
}
