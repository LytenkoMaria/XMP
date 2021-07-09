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

Route::get('/Calendar', function () {
    return view("bookingPhotosession");
})->name('event');

Route::get('/Profile', function () {
    return view("profile");
})->name('profile');

Route::get('/Gallery', function () {
    return view("gallery");
})->name('gallery');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/dnd', 'DnDController@index')->name('dnd');
Route::get('/dnd/get', 'DnDController@get');
Route::get('/xmp', 'xmpController@index')->name('xmp');
Route::post('/xmp/get', 'xmpController@getXMP');
Route::post('/xmp/set', 'xmpController@setXMP');
Route::post('/xmp/new/set', 'xmpController@setNewXMP');
Route::post('/xmp/show', 'xmpController@showXMP');
Route::post('/xmp/change', 'xmpController@changeXMP');
Route::get('/get/user/new/xmp', 'xmpController@getUserXMP');
Route::post('/booking/photosession', 'PhotosessionController@booking');
Route::post('/change/booking/photosession', 'PhotosessionController@change');
Route::get('/booking/get/photosession', 'PhotosessionController@get');
Route::post('/get/event/details', 'PhotosessionController@getDatails');
Route::post('/get/user/chose/xmp', 'PhotosessionController@getChoseXMP');
Route::post('/profile/new', 'ProfileController@NewProfile');
Route::get('/profile/get', 'ProfileController@GetProfile');
Route::post('/directory/new', 'GalleryController@NewDirectory');
Route::get('/directory/get', 'GalleryController@GetDirectory');
