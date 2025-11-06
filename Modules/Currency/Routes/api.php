<?php

use Illuminate\Support\Facades\Route;
use Modules\Currency\Http\Controllers\Api\CurrencyController;

Route::group([
    'prefix' => 'v1',
], function () {
    Route::group([
        "middleware" => ["auth", "web"]
    ], function () {
        Route::apiResource('currencies', CurrencyController::class, ['except' => ['index', 'show']]);
        Route::group([
            'prefix' => 'currencies',
            'as' => 'currencies.'
        ], function () {
            Route::get("check-code", [CurrencyController::class, "checkCode"])->name('check-code');
            Route::get("update-rates", [CurrencyController::class, "updateRates"])->name('update-rates');
        });
    });

    Route::apiResource('currencies', CurrencyController::class, ['except' => ['store', 'update', 'destroy']]);
});
