<?php

use Illuminate\Http\Request;

// APP LOGIN
Route::post('login', 'AuthController@login');
Route::post('register', array('before' => 'csrf', 'as' => 'register', 'uses' => 'AuthController@register'));


// FACEBOOK LOGIN
Route::get('facebook-login', 'AuthController@facebookLogin');



// SERVICES CATEGORIES
Route::group(['prefix' => 'service-category'], function () {
    // show data
    Route::get('/', 'ServicesCategoryController@index');
    // store data
    Route::post('/add', 'ServicesCategoryController@store');
    // update data
    Route::post('/edit', 'ServicesCategoryController@update');
    // delete data
    Route::get('/delete', 'ServicesCategoryController@destroy');
});

// All Services
Route::get('services', 'ServicesController@allServices')->name('services');

// All User Rating reviews
Route::get('reviews', 'UserController@allReviews');

// SEARCH SERVICES
Route::post('services-search', 'ServicesController@search');

// ALL USERS
Route::get('users', 'UserController@allUsers');
// SINGLE USER
Route::get('user/{id}', 'UserController@singleUser');
// DELETE USER
Route::delete('user/{id}', 'UserController@deleteUser');

// ALL USER TRANSACTION DETAIL
Route::get('/users_transaction_detail/{id}', 'PaymentController@usersTransactionDetail');

Route::middleware('auth:api')->group(function () {

    Route::post('user/change-password', 'AuthController@changePassword');

    // Notifications
    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', 'NotificationsController@index');
        Route::post('/add', 'NotificationsController@store');
        Route::get('/delete', 'NotificationsController@destroy');
    });

    // EMAIL VERIFY
    Route::group(['prefix' => 'email'], function () {
        Route::post('/otp', 'AuthController@sendEmailVerifyOtp');
        Route::post('/verify', 'AuthController@verifyEmail');
    });

    // USER
    Route::group(['prefix' => 'user'], function () {
        // show data
        Route::get('/', 'UserController@index');
        // update data
        Route::post('/edit', 'UserController@update');
        // wallet
        Route::get('/wallet-balance', 'UserController@walletBalance');
    });

    // SERVICES
    Route::group(['prefix' => 'service'], function () {
        // show data
        Route::get('/', 'ServicesController@index');
        // store data
        Route::post('/add', 'ServicesController@store');
        // update data
        Route::post('/edit/{id}', 'ServicesController@update');
        // delete data
        Route::get('/delete/{id}', 'ServicesController@destroy');
    });


    // SWAP SERVICES
    Route::group(['prefix' => 'swap-service'], function () {
        // show data
        Route::get('/', 'SwapServicesController@index');
        // store data
        Route::post('/add', 'SwapServicesController@store');
        // show data
        Route::get('/show', 'SwapServicesController@show');
        // update data
        Route::post ('/edit/{id}', 'SwapServicesController@update');
        // delete data
        Route::get('/delete/{id}', 'SwapServicesController@destroy');
        // Change Status
        Route::get('/change-status', 'SwapServicesController@changeStatus');
        //  Get Disputes
        Route::get('/disputes','SwapServicesController@getDisputes');
        //  Store Dispute
        Route::post('/disputes','SwapServicesController@storeDispute');
        // Update Dispute Status
        Route::put('/disputes/{id}','SwapServicesController@updateDispute');
        // Update Dispute Status
        Route::put('update-dispute-comment','SwapServicesController@updateDisputeComments');
    });

    // HIRE SERVICES
    Route::group(['prefix' => 'hire-service'], function () {
        // show data
        Route::get('/', 'HireServicesController@index');
        // store data
        Route::post('/add', 'HireServicesController@store');
        // show data
        Route::get('/show', 'HireServicesController@show');
        // delete data
        Route::get('/delete/{id}', 'HireServicesController@destroy');
        // update data
        Route::post('/edit/{id}', 'HireServicesController@update');
    });

    // SWAP SERVICES
    Route::group(['prefix' => 'swap-hire-services'], function () {
        Route::get('show', 'SwapHireServicesController@index');
    });

    // USER CERTIFICATIONS
    Route::group(['prefix' => 'certifications'], function () {
        // show data location
        Route::get('/', 'CertificationsController@index');
        // store certification data
        Route::post('/add', 'CertificationsController@store');
        // update certification data
        Route::post('/edit/{id}', 'CertificationsController@update');
        // delete certification data
        Route::get('/delete/{id}', 'CertificationsController@destroy');
    });

    // USER RATING
    Route::post('user-rating', 'UserController@userRating');

    // PAYMENT
    // BRAINTREE
    Route::group(['prefix' => 'braintree'], function () {
        // GENERATE CLIENT TOKEN
        Route::get('/getclienttoken', 'PaymentController@getClientToken');
        // MAKE PAYMENT PROCESS
        Route::post('/paymentprocess', 'PaymentController@processPayment');
        // GET TRANSACTION DETAIL FROM BRAINTREE
        Route::get('/braintree_transaction_detail', 'PaymentController@braintreeTransactionDetail');
        // GET TRANSACTION DETAIL FROM USERS TRANSACTIONS TABLE
        //Route::get('/users_transaction_detail', 'PaymentController@usersTransactionDetail');
    });

    // WITHDRAWAL
    Route::group(['prefix' => 'withdrawals'], function () {
        Route::get('/','WithdrawalController@index');
        Route::post('/','WithdrawalController@store');
        Route::get('/rate','WithdrawalController@getrate');
        Route::get('/{id}','WithdrawalController@show');
        Route::put('/{id}','WithdrawalController@update');
    });

    // ACCOUNT
    Route::group(['prefix' => 'accounts'], function () {
        //Accounts
        Route::get('/{user_id}','AccountsController@index');
        Route::post('/','AccountsController@store');
        Route::delete('/{id}','AccountsController@destroy');
    });


    // Phone number verify
    Route::post('phone-otp-generate', 'AuthController@sendSms');
    Route::post('phone-otp-verify', 'AuthController@phoneOtpVerify');

    // LOGOUT
    Route::get('logout', 'AuthController@logoutApi');
});



Route::middleware('admin')->group(function () {

});

Route::post('adminlogin', 'AdminController@login');




// PASSWORD RESET
Route::group(['namespace' => 'Auth','prefix' => 'password'], function () {
    Route::post('create', 'PasswordResetController@create');
    Route::post('reset', 'PasswordResetController@reset');
});

