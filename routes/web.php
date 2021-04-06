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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/dnd', 'DnDController@index')->name('dnd');
Route::get('/dnd/get', 'DnDController@get');
Route::get('/xmp', 'xmpController@index')->name('xmp');
Route::post('/xmp/get', 'xmpController@getXMP');
Route::post('/xmp/set', 'xmpController@setXMP');
Route::post('/xmp/show', 'xmpController@showXMP');
Route::post('/xmp/change', 'xmpController@changeXMP');
