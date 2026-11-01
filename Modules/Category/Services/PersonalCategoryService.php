<?php

namespace Modules\Category\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Category\Entities\Category;
use Modules\User\Entities\User;

class PersonalCategoryService
{
    public function owned(User $user, string $id): Category
    {
        $category = Category::whereKey($id)->lockForUpdate()->firstOrFail();
        if ($category->is_default || (string) $category->user_id !== (string) $user->id) {
            throw new AuthorizationException;
        }

        return $category;
    }

    public function store(User $user, array $input): Category
    {
        return DB::transaction(function () use ($user, $input) {
            // The user row serializes all personal-category writes, including the first creation.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if (Category::userId($user->id)->default(false)->count() >= Category::PERSONAL_LIMIT) {
                throw ValidationException::withMessages(['name' => __('category::validation.limit', ['limit' => Category::PERSONAL_LIMIT])]);
            }
            $this->validateName($user, $input['name']);

            return Category::create([...$input, 'user_id' => $user->id, 'is_default' => false]);
        });
    }

    public function update(User $user, string $id, array $input): Category
    {
        return DB::transaction(function () use ($user, $id, $input) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $category = $this->owned($user, $id);
            $this->validateName($user, $input['name'], $category->id);
            $category->update($input);

            return $category;
        });
    }

    public function archive(User $user, string $id, bool $archived): Category
    {
        return DB::transaction(function () use ($user, $id, $archived) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $category = $this->owned($user, $id);
            $category->archived_at = $archived ? ($category->archived_at ?? now()) : null;
            $category->save();

            return $category;
        });
    }

    public function destroy(User $user, string $id): void
    {
        DB::transaction(function () use ($user, $id) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $category = $this->owned($user, $id);
            if ($category->transactions()->exists() || $category->recurrings()->exists()) {
                throw ValidationException::withMessages(['category' => __('category::validation.in_use')]);
            }
            $category->delete();
        });
    }

    private function validateName(User $user, string $name, ?int $except = null): void
    {
        $normalize = fn ($value) => Str::lower(trim(preg_replace('/\s+/u', ' ', $value)));
        $candidate = $normalize($name);
        $categories = Category::visibleTo($user->id)->when($except, fn ($query) => $query->whereKeyNot($except))->get();
        foreach ($categories as $category) {
            // Compare all translations of defaults, and include archived personal names.
            foreach ((array) $category->name as $existing) {
                if (is_string($existing) && $normalize($existing) === $candidate) {
                    throw ValidationException::withMessages(['name' => __('category::validation.duplicate')]);
                }
            }
        }
    }
}
