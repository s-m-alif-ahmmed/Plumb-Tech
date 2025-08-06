<?php

use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\BankDetailsController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\ConversationController;
use App\Http\Controllers\API\DiscussionRequestController;
use App\Http\Controllers\API\EngineerController;
use App\Http\Controllers\API\FirebaseTokenController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\PayPalController;
use App\Http\Controllers\API\ReportIssueController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\ServiceFeeController;
use App\Http\Controllers\API\SkillController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserAnswerController;
use App\Http\Controllers\API\VillageController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\WithdrawalRequestController;
use Illuminate\Support\Facades\Route;
use Random\Engine;



//Village route
Route::get('/skills', [SkillController::class, 'index']);

//Guest Routes
Route::group(['middleware' => 'guest:sanctum'], function ($router) {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('resend_otp', [RegisterController::class, 'resend_otp']);
    Route::post('verify_email', [RegisterController::class, 'verify_email']);
    Route::post('forgot-password', [RegisterController::class, 'forgot_password']);
    Route::post('verify-otp', [RegisterController::class, 'verify_otp']);
    Route::post('reset-password', [RegisterController::class, 'reset_password']);
});

//Authenticate Routes
Route::group(['middleware' => 'auth:sanctum'], function ($router) {
    //common routes
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/logout-all', [LoginController::class, 'logoutAll']);
    Route::post('/profile', [LoginController::class, 'profile_update']);
    Route::post('/change-password', [LoginController::class, 'change_password']);
    Route::get('/profile', [LoginController::class, 'profile']);
    Route::delete('/delete-portfolio/{id}', [LoginController::class, 'delete_portfolio']);

    //reviews routes
    Route::get('/reviews/users/list', [ReviewController::class, 'index']);
    Route::get('/reviews/average-rating', [ReviewController::class, 'averageRating']);

    //Notification api
    Route::get('/notifications', [NotificationController::class, 'index']);


    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);

    // Messages
    Route::get('/conversations/{id}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{id}/messages', [MessageController::class, 'store']);

    // Firebase Token Module
    Route::get("firebase/test", [FirebaseTokenController::class, "test"]);
    Route::post("firebase/token/add", [FirebaseTokenController::class, "store"]);
    Route::post("firebase/token/get", [FirebaseTokenController::class, "getToken"]);
    Route::post("firebase/token/delete", [FirebaseTokenController::class, "deleteToken"]);

    //routes for engineer only
    Route::middleware('role:engineer')->prefix('engineers')->group(function ($router) {
        Route::controller(EngineerController::class)->group(function ($router) {
            Route::get('/tasks', 'index');
            Route::get('/request-accept/{id}', 'acceptRequest')->name('request.accept');
            Route::post('/request-decline/{id}', 'declineRequest')->name('request.decline');
            Route::get('/working-history', 'workingHistory');
            Route::get('/working-history/{id}', 'workingHistoryDetails');
        });
        Route::controller(WalletController::class)->group(function () {
            Route::get('/balance', 'balance');
            Route::get('/income-history', 'incomeHistory');
        });

        //Bank details API
        Route::controller(BankDetailsController::class)->group(function () {
            Route::post('/bank-details', 'store');
            Route::get('/get/bank-details', 'index');
        });

        // Route to withdrawal request
        Route::controller(WithdrawalRequestController::class)->group(function () {
            Route::post('/withdrawal-request', 'store');
            Route::get('/get/withdrawal-requests', 'index');
        });
    });

    //routes for customer only
    Route::middleware('role:customer')->group(function ($router) {
        //reviews store api
        Route::post('/reviews', [ReviewController::class, 'store']);

        //service api
        Route::controller(ServiceController::class)->prefix('services')->group(function () {
            Route::get('/', 'index');
            Route::get('/questions/{id}', 'questions');
            Route::get('/skills/{id}', 'skills');
        });

        //send discussion request
        Route::controller(DiscussionRequestController::class)->group(function () {
            Route::post('/engineer/send-request', 'sendRequest');
            Route::get('/engineer-profile-with-services/{request_id}', 'engineerProfile');
        });

        //payment routes
        Route::controller(PayPalController::class)->prefix('paypal')->group(function () {
            Route::post('/pay', 'createPayment');
        });

        Route::post('/start-conversation', [ConversationController::class, 'store']);

        // Report Issues from user Against Engineer
        Route::post('/report-issues', [ReportIssueController::class, 'store']);
    });


    Route::controller(EngineerController::class)->group(function ($router) {
        Route::get('/working-history', 'workingHistory');
        Route::get('/working-history/{id}', 'workingHistoryDetails');
    });
});

Route::controller(PayPalController::class)->prefix('paypal')->group(function () {
    Route::get('payment-success', 'returnUrl')->name('paypal.return');
    Route::get('payment-cancel', 'cancelUrl')->name('paypal.cancel');
    Route::post('webhook', 'webhook');
});
