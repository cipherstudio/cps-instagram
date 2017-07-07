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

/*
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
*/

# BEGIN: CPS Instagram

Route::get('/', function () {
    return view('instagram.index.index');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');



// END: CPS Instagram


// @todo disable route login, register, forgot password


// api
Route::get('instagram/oauth', ['uses' => 'Instagram\SyncController@oauth', 'as' => 'instagram.sync.oauth']);


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();

    // @see vendor/tcg/voyager/routes/voyager.php
    $namespacePrefix = '\\'.config('voyager.controllers.namespace').'\\';
    Route::group(['middleware' => 'admin.user'], function () use ($namespacePrefix) {

        // @todo instagram-media/sync {closure}
        Route::get('instagram-media-sync', ['uses' => 'Instagram\SyncController@index', 'as' => 'instagram.sync.index']);
        Route::get('instagram-media-sync/load', ['uses' => 'Instagram\SyncController@load', 'as' => 'instagram.sync.load']);

    });

    


});
