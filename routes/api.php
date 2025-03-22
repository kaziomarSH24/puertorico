<?php

use App\Http\Controllers\Admin\AudioController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FeaturedController;
use App\Http\Controllers\Admin\ManageUser;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PricingPlanController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\AudioTrackController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Homecontroller;
use App\Http\Controllers\NearbyAudioController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
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
    Route::post('reset-password', 'resetPassword')->middleware('jwt.auth');
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
    //dashboard
    Route::get('/dashboard', [DashboardController::class, 'dashboard']);
    //category controller
    Route::apiResource('category', CategoryController::class)->except(['create', 'edit']);
    //audio controller
    Route::apiResource('audio', AudioController::class)->except(['create', 'edit']);

    //pricingPlan controller
    Route::get('pricing-plan', [PricingPlanController::class, 'getAllPlan'])->withoutMiddleware('check.admin');
    Route::post('pricing-plan/update', [PricingPlanController::class, 'updateOrCreatePlan']);

    //page controller
    Route::get('/pages/{type}', [PageController::class, 'getPageContent']);
    Route::post('/pages/update', [PageController::class, 'updateOrCreatePage']);
    //faq controller
    Route::apiResource('faq', FaqController::class)->except(['create', 'edit','show']);

    //manage user
    Route::get('/user', [ManageUser::class, 'getAllUsers']);
    Route::get('/user/{id}', [ManageUser::class, 'showUser']);
    Route::delete('user/{id}', [ManageUser::class, 'deleteUser']);

    //featured category add
    Route::apiResource('featured', FeaturedController::class)->except(['create', 'edit','show','update','destroy']);


});

//nearby audio
Route::middleware('jwt.auth')->group(function () {
    Route::post('find-nearby-audio', [NearbyAudioController::class, 'checkNearbyAudios']);
    Route::post('get-nearby-audio', [NearbyAudioController::class, 'getNearbyAudios']);
    Route::get('audio/{id}', [AudioController::class, 'show']);

    //homesection
    Route::get('home-section', [Homecontroller::class, 'homeSection']);
    Route::get('category/{id}/audio', [Homecontroller::class, 'categoryAudios']);

    //story
    Route::apiResource('story', StoryController::class)->except(['create', 'edit','show','update']);
    //favorite
    Route::apiResource('favorite', FavoriteController::class)->except(['create', 'edit','show','update']);
    //bookmark
    Route::apiResource('bookmark', BookmarkController::class)->except(['create', 'edit','show','update']);

    //pages
    Route::get('/pages/{type}', [PageController::class, 'getPageContent']);
    //faq
    Route::get('/faq', [FaqController::class, 'index']);

    //update user profile & password
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/update-password', [UserController::class, 'updatePassword']);

    //audio history
    Route::get('/audio-history', [AudioTrackController::class, 'getAudioHistory']);
    Route::post('/add-audio-history', [AudioTrackController::class, 'userAudioHistory']);
    Route::get('/audio-count', [AudioTrackController::class, 'countAudio']);

    //subscription
    Route::post('create-checkout-session', [SubscriptionController::class, 'createCheckoutSession']);

    //check is_subscribed_required
    Route::get('is-subscribed-required', [AudioTrackController::class, 'isSubscribedRequired']);
});

// Route::middleware(['jwt.auth','restrict.audio.access'])->group(function () {
//     Route::get('/storage/audio/{filename}', function ($filename) {
//         $filePath = storage_path("app/public/audio/$filename");

//         if (!file_exists($filePath)) {
//             return response()->json(['message' => 'Audio not found.'], 404);
//         }

//         return response()->file($filePath);
//     });
// });

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('/storage/audio/{filename}', function ($filename) {
        $filePath = storage_path("app/public/audio/$filename");

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Audio not found.'], 404);
        }

        return response()->file($filePath);
    });
});

//notification
Route::group(['middleware' => 'jwt.auth', 'prefix' => 'notifications'], function () {
    Route::get('/', [NotificationController::class, 'getNotifications']);
    Route::put('/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::put('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/delete', [NotificationController::class, 'deleteNotification']);
});

// Route::post('create-payment', [SubscriptionController::class, 'createPayment']);


//payment callback
Route::get('stripe-payment-success', [SubscriptionController::class, 'paymentSuccess'])->name('payment.verify');
Route::get('stripe-payment-cancel', [SubscriptionController::class, 'paymentCancel'])->name('payment.cancel');
