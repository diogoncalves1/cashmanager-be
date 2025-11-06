<?php

use Illuminate\Support\Facades\Route;
use Modules\SharedRoles\Http\Controllers\Api\SharedPermissionController;
use Modules\SharedRoles\Http\Controllers\Api\SharedRoleController;

Route::group(
    [
        'prefix' => 'v1/',
    ],
    function () {
        Route::group(
            [
                'as' => 'shared-roles.',
                'prefix' => 'shared-roles/',
                'middleware' => ['auth', 'web', 'setlocale']
            ],
            function () {
                Route::get('check-code', [SharedRoleController::class, 'checkRoleCode']);
            }
        );

        Route::group(
            [
                'as' => 'shared-permissions.',
                'prefix' => 'shared-permissions/',
                'middleware' => ['auth', 'web', 'setlocale']
            ],
            function () {
                Route::get('check-code', [SharedPermissionController::class, 'checkPermissionCode']);
            }
        );
    }
);
