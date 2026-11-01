<?php

namespace Modules\Category\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\ValidationException;
use Modules\Category\Entities\Category;
use Modules\User\Entities\User;

class SelectableCategory implements ValidationRule
{
    public function __construct(private int|string $userId, private ?string $type, private int|string|null $originalId = null) {}

    public function lockAndValidate(mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        // Match personal-category mutation lock order to avoid deletion/assignment races.
        User::whereKey($this->userId)->lockForUpdate()->firstOrFail();
        Category::whereKey($value)->lockForUpdate()->first();
        $this->validate('category_id', $value, function ($message) {
            throw ValidationException::withMessages(['category_id' => $message]);
        });
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Preserve a previously assigned category when editing a shared movement,
        // even when its owner has archived it. This never grants selection rights.
        $existing = $this->originalId !== null && (string) $value === (string) $this->originalId;
        $query = Category::whereKey($value)->when($this->type, fn ($query) => $query->type($this->type));
        if (! $existing) {
            $query->selectable($this->userId);
        }
        if (! $query->exists()) {
            $fail(__('category::validation.unavailable'));
        }
    }
}
