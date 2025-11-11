<?php

namespace Modules\UserPreferences\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPreferencesRepository
{
    public function update(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();

            $input = $request->only(['lang', 'currency_id']);

            $preferences = $user->preferences;

            $preferences->update($input);

            return $preferences;
        });
    }
}
