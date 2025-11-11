<?php

use Illuminate\Support\Facades\Route;
use Modules\UserPreferences\Http\Controllers\Api\UserPreferencesController;

Route::group([
    'prefix' => 'v1',
    'middleware' => ['auth:sanctum', 'setlocale']
], function () {
    Route::put('user-preferences', [UserPreferencesController::class, 'update']);
});
