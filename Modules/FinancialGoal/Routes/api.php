<?php

use Illuminate\Support\Facades\Route;
use Modules\FinancialGoal\Http\Controllers\Api\V1\FinancialGoalController;
use Modules\FinancialGoal\Http\Controllers\Api\V1\FinancialGoalTransactionController;
use Modules\FinancialGoal\Http\Controllers\Api\V1\FinancialGoalUserController;
use Modules\FinancialGoal\Http\Controllers\Api\V1\FinancialGoalUserInviteController;

Route::group([
    'prefix' => 'v1',
    'as'     => 'v1.',
], function () {
    Route::group(
        [
            'middleware' => ['auth:sanctum', 'setlocale'],
        ],
        function () {
            // Financial Goals
            Route::group([
                'as'     => 'financial-goals.',
                'prefix' => 'financial-goals',
            ], function () {
                Route::get('/all', [FinancialGoalController::class, 'allUser']);
                Route::get('/stats', [FinancialGoalController::class, 'getStats']);

                Route::get('/{id}/activity', [FinancialGoalController::class, 'activity']);

                // Updates
                Route::post('/{id}/cancel', [FinancialGoalController::class, 'cancel']);
                Route::post('/{id}/complete', [FinancialGoalController::class, 'complete']);
                Route::post('/{id}/reset', [FinancialGoalController::class, 'reset']);

                // Invites
                Route::post('/{id}/invite/{userId}', [FinancialGoalUserInviteController::class, 'invite']);
                Route::post('/{id}/accept', [FinancialGoalUserInviteController::class, 'accept']);
                Route::delete('/{id}/invite/{userId}', [FinancialGoalUserInviteController::class, 'destroy']);
                Route::post('/{id}/revoke', [FinancialGoalUserInviteController::class, 'revoke']);

                // Relations
                Route::post('/{id}/revoke-user/{userId}', [FinancialGoalUserController::class, 'revokeUser']);
                Route::put('/{id}/user-role/{userId}', [FinancialGoalUserController::class, 'updateUserRole']);
                Route::delete('/{id}/leave', [FinancialGoalUserController::class, 'leave']);
                // Route::get('/{id}/users', [FinancialGoalUserController::class, 'users']);
            });

            Route::apiResource('financial-goals', FinancialGoalController::class);

            // Financial Goal Transactionss
            Route::group([
                'as'     => 'financial-goal-transactions.',
                'prefix' => 'financial-goal-transactions',
            ], function () {
                Route::post('/{id}/confirm', [FinancialGoalTransactionController::class, 'confirm']);
            });

            Route::apiResource('financial-goal-transactions', FinancialGoalTransactionController::class);

        }
    );
});
