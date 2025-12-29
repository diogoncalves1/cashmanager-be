<?php

use Illuminate\Support\Facades\Route;
use Modules\Asset\Http\Controllers\AssetController;

Route::group([
    'middleware' => ['auth'],
    'as'         => 'admin.',
    'prefix'     => 'admin',
], function () {
    // Route::group([
    //     'as'     => 'assets.',
    //     'prefix' => 'assets',
    // ], function () {
    //     Route::get('/get/{ticker}', [AssetController::class, 'storeApi']);
    // });

    Route::resource('assets', AssetController::class)->names('assets');
});
