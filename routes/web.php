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

// home
Route::get('/', ['uses' => 'Instagram\IndexController@index', 'as' => 'instagram.index.index']);
Route::get('/load', ['uses' => 'Instagram\IndexController@load', 'as' => 'instagram.index.load']);
Route::get('/items/{id}', ['uses' => 'Instagram\IndexController@index', 'as' => 'instagram.index.index']);

// oauth
Route::get('instagram/oauth', ['uses' => 'Instagram\SyncController@oauth', 'as' => 'instagram.sync.oauth']);



// remove frontend auth
//Auth::routes();

// admin
Route::group(['prefix' => 'admin'], function () {
    
    Voyager::routes();
    

    // @see vendor/tcg/voyager/routes/voyager.php
    $namespacePrefix = '\\'.config('voyager.controllers.namespace').'\\';
    Route::group(['middleware' => 'admin.user'], function () use ($namespacePrefix) {

        // override
        Route::resource('instagram-media', '\\App\Http\Controllers\Instagram\\MediaController', [
            'only' => ['index'],
            'names' => [
                'index' => 'voyager.instagram-media.index'
            ]
        ]);

        // sync
        // @todo instagram-media/sync {closure}
        Route::get('instagram-media-sync', ['uses' => 'Instagram\SyncController@index', 'as' => 'instagram.sync.index']);
        Route::get('instagram-media-sync/load', ['uses' => 'Instagram\SyncController@load', 'as' => 'instagram.sync.load']);
        Route::post('instagram-media-sync/import', ['uses' => 'Instagram\SyncController@import', 'as' => 'instagram.sync.import']);

        // auto sync
        Route::post('instagram-media-sync/sync', ['uses' => 'Instagram\SyncController@sync', 'as' => 'instagram.sync.sync']);

        // points
        Route::get('instagram-media/points/{id}', ['uses' => 'Instagram\PointController@points', 'as' => 'instagram.point.points']);
        Route::post('instagram-point/save-points', ['uses' => 'Instagram\PointController@savePoints', 'as' => 'instagram.point.save-points']);


    });

    

});