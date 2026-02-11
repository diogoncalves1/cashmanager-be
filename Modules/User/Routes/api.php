<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\Api\UserController;

Route::group([
    'prefix' => 'v1',
], function () {
    Route::group([
        'middleware' => ["auth:sanctum", "setlocale"],
    ], function () {
        Route::put('me', [UserController::class, 'updateSettings']);
    });
});
