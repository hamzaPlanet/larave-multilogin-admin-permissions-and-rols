<?php

use Illuminate\Support\Facades\Route;

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

Route::namespace('Admin')->group(function (){

    Route::group(['middleware'=>['guest:admin']],function(){
        Route::get('/login','LoginController@showLogin')->name('admin.login');
        Route::post('/login','LoginController@login')->name('admin.login.submit');

        // password reset
        Route::post('password/email','ForgotPasswordController@sendResetLinkEmail')->name('admin.password.email');
        Route::get('password/reset','ForgotPasswordController@showLinkRequestForm')->name('admin.password.request');
        Route::post('password/reset','ResetPasswordController@reset')->name('admin.password.update');
        Route::get('password/reset/{token}','ResetPasswordController@showResetForm')->name('admin.password.reset');
    });

    Route::group(['middleware' => ['auth:admin','verified-admin']],function(){

        // dashboard routes

        Route::resource('roles','RolesController')->except('show');
        Route::resource('admins','AdminsController')->except('show');

//        Setting
        Route::get('settings','SettingsController@index')->name('admin.settings.index');
        Route::get('settings/social-links','SettingsController@social_links')->name('admin.social.links');
        Route::get('settings/social-login','SettingsController@social_login')->name('admin.social.login');
        Route::post('settings','SettingsController@store')->name('admin.settings.store');

        Route::get('/register','RegisterController@showRegistrationForm')->name('admin.register');
        Route::post('/register','RegisterController@create')->name('admin.register');

        Route::get('/','HomeController@index')->name('admin.home');
        Route::get('/home','HomeController@index')->name('admin.home');
        Route::post('/logout','LoginController@adminLogout')->name('admin.logout');

    });


    Route::group(['middleware' => ['auth:admin']],function(){

        // email verification
        Route::post('email/resend','VerificationController@resend')->name('admin.verification.resend');
        Route::get('email/verify','VerificationController@show')->name('admin.verification.notice');
        Route::get('email/verify/{id}/{hash}','VerificationController@verify')->name('admin.verification.verify');

    });


});

// POST     | password/email         | password.email     | ForgotPasswordController@sendResetLinkEmail
//
// GET|HEAD | password/reset         | password.request   | ForgotPasswordController@showLinkRequestForm
//
// POST     | password/reset         | password.update    | ResetPasswordController@reset
//
// GET|HEAD | password/reset/{token} | password.reset     | ResetPasswordController@showResetForm

//
// POST     | email/resend                     | verification.resend    | App\Http\Controllers\Auth\VerificationController@resend                 | web auth
//          |                                  |                        |                                                                         |
//          |                                  |                        |                                                                         | throttle:6,1
// GET|HEAD | email/verify                     | verification.notice    | App\Http\Controllers\Auth\VerificationController@show                   | web auth
//          |                                  |                        |                                                                         |
// GET|HEAD | email/verify/{id}/{hash}         | verification.verify    | App\Http\Controllers\Auth\VerificationController@verify                 | web
