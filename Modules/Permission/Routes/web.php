<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\PermissionController;

Route::group([
    "prefix" => "admin",
    "as" => "admin.",
    "middleware" => ["auth"]
], function () {
    Route::group(
        [
            "prefix" => "roles",
            "as" => "roles."
        ],
        function () {
            Route::get("manage/{id}", [\Modules\Permission\Http\Controllers\RoleController::class, "showManageForm"])->name('manage');
            Route::post("manage/{id}", [\Modules\Permission\Http\Controllers\RoleController::class, "manage"])->name('manage.update');
        }
    );
    Route::resource("roles", \Modules\Permission\Http\Controllers\RoleController::class, ['except' => ['show']]);
    Route::get('permissions/data', [PermissionController::class, 'dataTable']);
    Route::resource("permissions", PermissionController::class, ['except' => ['show']]);
});
