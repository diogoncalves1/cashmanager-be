<?php

use Modules\Friends\Http\Controllers\Api\FriendshipController;
use Illuminate\Support\Facades\Route;
use Modules\Friends\Http\Controllers\Api\FriendshipRequestController;

Route::group(
    [
        'as' => 'v1.',
        'prefix' => 'v1',
    ],
    function () {
        Route::group(
            [
                'middleware' => ['auth:sanctum', 'setlocale']
            ],
            function () {
                Route::group([
                    'as' => 'friendships.',
                    'prefix' => 'friendships'
                ], function () {
                    Route::delete('/{id}/remove', [FriendshipController::class, 'remove'])->name('remove');
                    Route::post('/{id}/block', [FriendshipController::class, 'block'])->name('block');
                    Route::post('/{id}/unblock', [FriendshipController::class, 'unblock'])->name('unblock');
                    Route::get('/', [FriendshipController::class, 'listFriends']);
                    Route::get('/blocked', [FriendshipController::class, 'listBlocked']);
                });
                Route::group([
                    'as' => 'friendship-requests',
                    'prefix' => 'friendship-requests'
                ], function () {
                    Route::post('{id}/send', [FriendshipRequestController::class, 'send'])->name('send');
                    Route::post('{id}/accept', [FriendshipRequestController::class, 'accept'])->name('accept');
                    Route::delete('{id}/decline', [FriendshipRequestController::class, 'decline'])->name('decline');
                    Route::get('pending', [FriendshipRequestController::class, 'listPending']);
                });
            }
        );
    }
);
