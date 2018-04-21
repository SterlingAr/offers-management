<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'auth:api'], function () {

    Route::post('logout', 'Auth\LoginController@logout');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::patch('settings/profile', 'Settings\UpdateProfile');
    Route::patch('settings/password', 'Settings\UpdatePassword');


    Route::get('/offers','OfferController@index');
    Route::get('/offers/{offer_id}', 'OfferController@show');
    Route::post('/offers/add', 'OfferController@store');
    Route::post('/offers/update/{offer}', 'OfferController@update');

    Route::get('/offers-list','OfferController@getOffersList');
    Route::get('/available-rooms', 'OfferController@getAvailableRooms');



    Route::get('/locations', 'LocationController@index');
    Route::post('/locations/search', 'LocationController@indexTable');
    Route::get('/locations/{location_id}', 'LocationController@show');
    Route::post('/locations/store', 'LocationController@store');


    Route::get('/rooms', 'RoomController@index');


});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login')->name('login');
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
});
