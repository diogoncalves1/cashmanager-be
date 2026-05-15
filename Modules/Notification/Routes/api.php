<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\Api\V1\NotificationController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::group([
        'as'     => 'notifications.',
        'prefix' => 'notifications',
    ], function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/feed', [NotificationController::class, 'feed'])->name('feed');
        Route::post('/{id}/archive', [NotificationController::class, 'markAsArchived'])->name('archive');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });
});
