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

    Route::get('/available-rooms', 'OfferController@getAvailableRooms');
    Route::post('/coupons', 'CouponsController@create');
    Route::get('/coupons', 'CouponsController@get');
    Route::get('/rooms', 'RoomController@index');


    Route::get('/offers','OfferController@index');
    Route::post('/offers/search','OfferController@indexTable');
    Route::get('/offers/{offer_id}', 'OfferController@show');
    Route::get('/offers/{offer_id}/dates', 'OfferController@dates');
    Route::post('/offers/add', 'OfferController@store');
    Route::post('/offers/update', 'OfferController@update');
    Route::delete('/offers/delete/{offer_id}', 'OfferController@destroy');
    Route::get('/offers-list','OfferController@getOffersList');


    Route::get('/locations', 'LocationController@index');
    Route::post('/locations/search', 'LocationController@indexTable');
    Route::get('/locations/{id}', 'LocationController@show');
    Route::post('/locations/store', 'LocationController@store');
    Route::post('/locations/update/{location_id}', 'LocationController@update');
    Route::delete('/locations/delete/{location_id}', 'LocationController@destroy');

    Route::post('/sales/search', 'SalesController@indexTable');
    Route::get('/sales/{sale_id}', 'SalesController@show');
    Route::post('/sales/add', 'SalesController@store');
    Route::post('/sales/update/{sale_id}', 'SalesController@update');
    Route::delete('/sales/delete/{sale_id}', 'SalesController@delete');



});

Route::group(['middleware' => 'guest:api'], function () {

    Route::post('login', 'Auth\LoginController@login')->name('login');
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
});
