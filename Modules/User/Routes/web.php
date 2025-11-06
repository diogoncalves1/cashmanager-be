<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::group(
    [
        "prefix" => "admin",
        "as" => "admin.",
        "middleware" => ["auth"]
    ],
    function () {
        Route::group([
            'prefix' => 'users/manage',
            'as' => 'users.manage'
        ], function () {
            Route::get('/{id}', [UserController::class, 'showManageForm']);
            Route::post('/{id}', [UserController::class, 'manage'])->name('.update');
        });
        Route::resource('users', UserController::class, ['except' => 'show']);
    }
);
