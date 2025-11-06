<?php

use Illuminate\Support\Facades\Route;

Route::group([
    "as" => "admin.",
    "prefix" => "admin",
    "middleware" => "auth"
], function () {
    Route::resource('currencies', \Modules\Currency\Http\Controllers\CurrencyController::class, ['except' => ['store', 'update', 'destroy', 'show']]);
});
