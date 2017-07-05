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
    return view('instagram.index');
});

Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

// END: CPS Instagram


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});
