<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::controller(AuthController::class)->group(function(){
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::get('/logout', 'logout')->middleware('jwt.auth');
    Route::post('/verify-email', 'verifyEmail');
    Route::post('/resent-otp', 'resendOtp');
    Route::post('reset-password', 'resetPassword');
    Route::post('forgot-password', 'forgotPassword');
    Route::get('validate-token', 'validateToken')->middleware('jwt.auth');
    //update user profile & password
    // Route::post('/update-profile', 'updateProfile')->middleware('jwt.auth');
    Route::post('/update-password', 'updatePassword')->middleware('jwt.auth');

    //social login
    Route::post('/auth/social-login', 'socialLogin');
});

//Admin routes
Route::group(['prefix' => 'admin', 'middleware' => ['jwt.auth','check.admin']], function () {
    //category controller
    Route::apiResource('category', CategoryController::class)->except(['create', 'edit']);

});
