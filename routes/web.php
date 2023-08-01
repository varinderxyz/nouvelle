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


Route::get('test','UserController@test');

Route::get('services', 'UserController@webTesting');
Route::get('services/all', 'ServicesController@webTesting');

Route::view('/bulksms', 'bulksms');


Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
Route::get('/callback/{provider}', 'SocialController@callback');

Route::get('/redirect', 'SocialAuthFacebookController@redirect');
Route::get('/callback', 'SocialAuthFacebookController@callback');

// braintree payments 
Route::get('/payment/process', 'PaymentController@process')->name('payment.process');


// BRAINTREE 
// Route::get('/braintreepayments', function () {
//     return view('braintreedropin');
// }); 
