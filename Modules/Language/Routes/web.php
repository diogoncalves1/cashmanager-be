<?php

use Illuminate\Support\Facades\Route;
use Modules\Language\Http\Controllers\LanguageController;

Route::group([
    'prefix'     => 'admin',
    'as'         => 'admin.',
    'middleware' => 'auth',
], function () {
    Route::resource('languages', LanguageController::class)->names('languages');
});
