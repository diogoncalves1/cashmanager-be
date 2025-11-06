<?php

use Modules\SharedRoles\Http\Controllers\SharedPermissionController;
use Modules\SharedRoles\Http\Controllers\SharedRoleController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'as' => 'admin.',
        'prefix' => 'admin/',
        'middleware' => ['auth', 'setlocale']
    ],
    function () {
        Route::group(
            [
                'as' => 'shared-roles.',
                'prefix' => 'shared-roles/'
            ],
            function () {
                Route::get('{id}/manage', [SharedRoleController::class, 'showManageForm'])->name('manage');
                Route::put('{id}/manage', [SharedRoleController::class, 'manage'])->name('update-permissions');
            }
        );
        Route::resource('shared-roles', SharedRoleController::class, ['except' => ['show']]);
        Route::resource('shared-permissions', SharedPermissionController::class, ['except' => ['show']]);
    }
);
