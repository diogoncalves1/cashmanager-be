<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounts\Http\Controllers\Api\V1\AccountController;
use Modules\Accounts\Http\Controllers\Api\V1\AccountUserController;
use Modules\Accounts\Http\Controllers\Api\V1\AccountUserInviteController;
use Modules\Accounts\Http\Controllers\Api\V1\TransactionController;

Route::group([
    'prefix' => 'v1',
    'as'     => 'api.v1.',
], function () {
    Route::group(
        [
            'middleware' => ['auth:sanctum', 'setlocale'],
        ],
        function () {
            // Accounts
            Route::group([
                'prefix' => 'accounts',
                'as'     => 'accounts.',
            ], function () {
                // Invites
                Route::post('/{id}/invite/{userId}', [AccountUserInviteController::class, 'invite']);
                Route::post('/{id}/accept', [AccountUserInviteController::class, 'accept']);
                Route::delete('/{id}/invite/{userId}', [AccountUserInviteController::class, 'destroy']);
                Route::post('/{id}/revoke', [AccountUserInviteController::class, 'revoke']);

                // Relations
                Route::post('/{id}/revoke-user/{userId}', [AccountUserController::class, 'revokeUser']);
                Route::put('/{id}/user-role/{userId}', [AccountUserController::class, 'updateUserRole']);
                Route::delete('/{id}/leave', [AccountUserController::class, 'leave']);
            });

            Route::apiResource('accounts', AccountController::class);

            // Transactions
            Route::apiResource('transactions', TransactionController::class);

            Route::group([
                'as'     => 'transactions.',
                'prefix' => 'transactions',
            ], function () {
                Route::post('/{id}/confirm', [TransactionController::class, 'confirm']);
            });
        }
    );
});
