<?php

use Illuminate\Support\Facades\Route;
use Modules\Language\Http\Controllers\Api\V1\LanguageController;

Route::group(
    [
        'as'     => 'v1.',
        'prefix' => 'v1',
    ],
    function () {
        Route::group(
            [
                'middleware' => ['auth:sanctum', 'setlocale'],
            ],
            function () {
                Route::get('/languages', [LanguageController::class, 'index']);
            }
        );
    }
);
