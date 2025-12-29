<?php

use Illuminate\Support\Facades\Route;
use Modules\Asset\Http\Controllers\Api\V1\AssetController;

Route::group([
    // 'middlware' => ['auth:sanctum'],
    'prefix' => 'v1',
], function () {
    Route::group([
        'prefix' => 'assets',
    ], function () {
        Route::get('/{ticker}/show', [AssetController::class, 'showByTicker']);
        Route::get('/search', [AssetController::class, 'search']);
        Route::get('/{id}', [AssetController::class, 'show']);
    });

    Route::get('asset', [AssetController::class, 'index']);
});
