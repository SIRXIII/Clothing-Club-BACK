<?php

use App\Http\Controllers\Api\Auth\LoginController as AuthLoginController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\PartnerAuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Api\RiderController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\TravelerController;
use App\Http\Controllers\Api\UserTwoFactorController;
use App\Http\Controllers\SupportMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthLoginController::class, 'login'])->middleware('throttle:6,1');;
Route::post('/two-factor/verify', [AuthLoginController::class, 'verify']);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->middleware('throttle:5,1');

Route::get('/social/providers', [SocialAuthController::class, 'getProviders']);
Route::get('/social/{provider}/redirect', [SocialAuthController::class, 'redirectToProvider']);
Route::get('/social/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);
Route::post('/social/{provider}/token', [SocialAuthController::class, 'loginWithToken']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
            'message' => 'User data retrieved successfully'
        ]);
    });

    Route::post('/2fa/generate-totp', [UserTwoFactorController::class, 'generateTotp']);
    Route::post('/2fa/verify-recovery', [UserTwoFactorController::class, 'regenerateTotpQr']);
    Route::post('/2fa/update', [UserTwoFactorController::class, 'updateTwoFactor']);

    Route::post('/social/unlink', [SocialAuthController::class, 'unlinkSocialAccount']);
});


Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [LoginController::class, 'logout']);


    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('states', [DashboardController::class, 'getStates']);
        Route::get('travelers', [DashboardController::class, 'travelersOverview']);
        Route::get('topPartners', [DashboardController::class, 'topPartners']);
        Route::get('/orders/queue', [DashboardController::class, 'queue']);
         Route::get('/notifications/latest', [DashboardController::class, 'latestAlert']);
    });
    // Travelers
    Route::get('/travelers', [TravelerController::class, 'index']);
    Route::get('/travelers/{id}', [TravelerController::class, 'show']);
    Route::post('/travelers/bulk-update', [TravelerController::class, 'bulkUpdate']);
    Route::post('/travelers/export', [TravelerController::class, 'export']);
    Route::delete('/travelers/{id}', [TravelerController::class, 'destroy']);


    //Partners
    Route::get('/partners', [PartnerController::class, 'index']);
    Route::get('/partners/{id}', [PartnerController::class, 'show']);
    Route::post('/partners/status-update', [PartnerController::class, 'statusUpdate']);
    Route::post('/partners/request-information', [PartnerController::class, 'sendEmail']);
    Route::post('/partner/store', [PartnerController::class, 'store']);
    Route::get('/partners/{id}/documents/download', [PartnerController::class, 'downloadDocuments']);


    // Riders
    Route::apiResource('riders', RiderController::class);
    Route::get('/riders/{id}/documents/download', [RiderController::class, 'downloadDocuments']);
    Route::post('/riders/status-update', [RiderController::class, 'statusUpdate']);
    Route::post('/riders/store', [RiderController::class, 'store']);
    Route::post('/riders/update/{id}', [RiderController::class, 'update']);
    Route::get('riders/{rider}/ratings', [RatingController::class, 'index']);


    // Products
    Route::apiResource('/products', ProductController::class);
    Route::post('/products/status-update', [ProductController::class, 'statusUpdate']);
    Route::post('products/bulk-delete', [ProductController::class, 'bulkDelete']);


    // Orders
    Route::apiResource('/orders', OrderController::class);
    Route::post('/orders/assign-rider', [OrderController::class, 'assignRider']);


    Route::apiResource('refunds', RefundController::class);
    Route::post('/refunds/status-update', [RefundController::class, 'updateStatus']);
    Route::get('/refunds/isChatSupported', [RefundController::class, 'isChatSupported']);

    Route::get('/support-tickets', [SupportTicketController::class, 'index']);
    Route::post('/support-tickets', [SupportTicketController::class, 'store']);

    Route::post('/support-tickets/{ticket}/status', [SupportTicketController::class, 'updateStatus']);
    Route::post('/support/check-or-create', [SupportTicketController::class, 'checkOrCreate']);



    Route::get('/support-tickets/{ticket}/messages', [SupportMessageController::class, 'index']);

    Route::post('/support-tickets/messages', [SupportMessageController::class, 'store']);


     Route::post('/profile/update', [LoginController::class, 'updateProfile']);
    Route::post('/user/update-password', [LoginController::class, 'updatePassword']);



    // Route::post('/products', [ProductController::class, 'store']);

});


// Route::prefix('partner')->group(function () {
//     Route::post('/login', [PartnerAuthController::class, 'login']);
//     Route::post('/logout', [PartnerAuthController::class, 'logout'])->middleware('auth:partner');

//     Route::middleware('auth:partner')->group(function () {
//         Route::get('/products', [ProductController::class, 'index']);
//         Route::get('/orders', [OrderController::class, 'index']);
//         Route::get('/refunds', [RefundController::class, 'index']);
//         Route::get('/support-tickets', [SupportTicketController::class, 'index']);
//     });
// });


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
