<?php

use Illuminate\Support\Facades\Route;
use Modules\DashboardCharts\Http\Controllers\Api\V1\DashboardChartsController;

Route::group([
    'middleware' => ['auth:sanctum'],
    'prefix'     => 'v1',
], function () {
    Route::get('dashboard-overview', [DashboardChartsController::class, 'overview'])->name('overview');

});
