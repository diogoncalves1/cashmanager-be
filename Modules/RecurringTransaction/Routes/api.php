<?php

use Illuminate\Support\Facades\Route;
use Modules\RecurringTransaction\Http\Controllers\Api\V1\RecurringTransactionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('recurrings', RecurringTransactionController::class);

    Route::group([
        'prefix' => 'recurrings',
        'as'     => 'recurrings.',
    ], function () {
        Route::post('/{id}/toggle', [RecurringTransactionController::class, 'toggle']);
    });
});
