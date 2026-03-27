<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '', 'as' => 'admin.', 'middleware' => ['auth' /*, 'admin'*/]], function () {
    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
        /**
         * EmailLayouts
         */
        Route::group(['prefix' => 'emailLayouts', 'as' => 'emailLayouts.'], function () {
            Route::get('/create/{id}', ['as' => 'create', 'uses' => 'EmailLayoutController@create']);
        });
        Route::resource('emailLayouts', 'EmailLayoutController',
            ['except' => ['show', 'create', 'store']]);
    });
    /**
     * EmailTypes
     */
    Route::resource('emailTypes', 'EmailTypeController', ['except' => ['show']]);

});
