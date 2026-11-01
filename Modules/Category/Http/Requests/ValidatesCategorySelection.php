<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Modules\Accounts\Entities\Transaction;
use Modules\Category\Rules\SelectableCategory;
use Modules\RecurringTransaction\Entities\RecurringTransaction;

trait ValidatesCategorySelection
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('category_id') || $validator->errors()->has('category_id') || $validator->errors()->has('type')) {
                return;
            }

            $original = null;
            if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                if ($id = $this->route('transaction')) {
                    $original = Transaction::find($id);
                } elseif ($id = $this->route('recurring')) {
                    $original = RecurringTransaction::find($id);
                }
            }

            $rule = new SelectableCategory($this->user()?->id ?? 0, $original?->type ?? $this->input('type'), $original?->category_id);
            $rule->validate('category_id', $this->input('category_id'), fn ($message) => $validator->errors()->add('category_id', $message));
        });
    }
}
