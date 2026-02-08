<?php

use Illuminate\Support\Facades\Route;
use Modules\Debts\Http\Controllers\Api\V1\DebtController;
use Modules\Debts\Http\Controllers\Api\V1\DebtPaymentController;
use Modules\Debts\Http\Controllers\Api\V1\DebtUserController;
use Modules\Debts\Http\Controllers\Api\V1\DebtUserInviteController;

Route::group([
    'prefix' => 'v1',
], function () {
    Route::group([
        'middleware' => ["auth:sanctum", "setlocale"],
    ], function () {
        // Debts
        Route::group([
            'prefix' => 'debts',
            'as'     => 'debts.',
        ], function () {
            Route::get('/all', [DebtController::class, 'allUser']);
            Route::get('/stats', [DebtController::class, 'getStats']);

            Route::get('/{id}/activity', [DebtController::class, 'activity']);
            // Updates
            Route::post('/{id}/mark-paid', [DebtController::class, 'markPaid']);
            Route::post('/{id}/reset', [DebtController::class, 'reset']);

            // Invites
            Route::post('/{id}/invite/{userId}', [DebtUserInviteController::class, 'invite']);
            Route::post('/{id}/accept', [DebtUserInviteController::class, 'accept']);
            Route::delete('/{id}/invite/{userId}', [DebtUserInviteController::class, 'destroy']);
            Route::post('/{id}/revoke', [DebtUserInviteController::class, 'revoke']);

            // Relations
            Route::post('/{id}/revoke-user/{userId}', [DebtUserController::class, 'revokeUser']);
            Route::put('/{id}/user-role/{userId}', [DebtUserController::class, 'updateUserRole']);
            Route::delete('/{id}/leave', [DebtUserController::class, 'leave']);
        });

        Route::apiResource('debts', DebtController::class);

        // Debt Payments
        Route::group(['prefix' => 'debt-payments', 'as' => 'debts-payments.'], function () {
            Route::post('/{id}/confirm', [DebtPaymentController::class, 'confirm']);
        });
        Route::apiResource('debt-payments', DebtPaymentController::class);
    });
});
