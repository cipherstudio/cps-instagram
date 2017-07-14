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

Route::get('/', ['uses' => 'Instagram\IndexController@index', 'as' => 'instagram.index.index']);
Route::get('/load', ['uses' => 'Instagram\IndexController@load', 'as' => 'instagram.index.load']);


Auth::routes();

// @todo disable route login, register, forgot password
// Route::get('/home', 'HomeController@index')->name('home');



// END: CPS Instagram





// api
Route::get('instagram/oauth', ['uses' => 'Instagram\SyncController@oauth', 'as' => 'instagram.sync.oauth']);


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();

    // @see vendor/tcg/voyager/routes/voyager.php
    $namespacePrefix = '\\'.config('voyager.controllers.namespace').'\\';
    Route::group(['middleware' => 'admin.user'], function () use ($namespacePrefix) {

        // sync
        // @todo instagram-media/sync {closure}
        Route::get('instagram-media-sync', ['uses' => 'Instagram\SyncController@index', 'as' => 'instagram.sync.index']);
        Route::get('instagram-media-sync/load', ['uses' => 'Instagram\SyncController@load', 'as' => 'instagram.sync.load']);
        Route::post('instagram-media-sync/import', ['uses' => 'Instagram\SyncController@import', 'as' => 'instagram.sync.import']);

        // points
        Route::get('instagram-media/points/{id}', ['uses' => 'Instagram\PointController@points', 'as' => 'instagram.point.points']);
        Route::post('instagram-point/save-points', ['uses' => 'Instagram\PointController@savePoints', 'as' => 'instagram.point.save-points']);

        // frontend



    });

    


});
