<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\CategoryController;

Route::group([
    'prefix' => 'v1/',
], function () {
    Route::middleware(['auth:sanctum', 'setlocale'])->apiResource('categories', \Modules\Category\Http\Controllers\Api\CategoryController::class);
});
