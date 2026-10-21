<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\BroadcastNotificationController;
use Modules\Notification\Http\Controllers\NotificationKeywordController;
use Modules\Notification\Http\Controllers\NotificationTypeController;

Route::group(
    [
        "prefix"     => "admin",
        "as"         => "admin.",
        "middleware" => ["auth", "admin"],
    ],
    function () {
        Route::resource('notifications', BroadcastNotificationController::class);

        Route::resource('notification-keywords', NotificationKeywordController::class, ['except' => 'show'])->names('notificationKeywords');

        Route::resource('notification-types', NotificationTypeController::class)->names('notificationTypes');

        Route::group([
            'as'     => 'notificationTypes.manage.',
            'prefix' => 'notification-types/{id}',
        ], function () {
            Route::get('manage', [NotificationTypeController::class, 'showManageForm'])->name('form');
            Route::put('manage', [NotificationTypeController::class, 'manage'])->name('update');
        });
    }
);
