<?php

use App\Http\Controllers\Api\V1\Auth\GuestAuthController;
use App\Http\Controllers\Api\V1\Auth\MemberAuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CustomerLoyaltyController;
use App\Http\Controllers\Api\V1\GiftCardController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\OrderItemController;
use App\Http\Controllers\Api\V1\OrderStatusController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\Api\V1\QrisController;
use App\Http\Controllers\Api\V1\ToppingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // -------------------------------------------------------------------------
    // Public endpoints
    // -------------------------------------------------------------------------
    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/toppings', [ToppingController::class, 'index']);
    Route::get('/menus/{product}', [MenuController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);

    Route::post('/auth/guest', [GuestAuthController::class, 'store']);
    Route::post('/auth/member', [MemberAuthController::class, 'login']);
    Route::get('/promotions', [PromotionController::class, 'index']);

    Route::middleware('auth:sanctum')->get('/auth/validate', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });

    // -------------------------------------------------------------------------
    // Webhook (Public — dipanggil oleh payment gateway, tanpa Sanctum auth)
    // -------------------------------------------------------------------------
    Route::prefix('webhooks')->group(function () {
        Route::post('/midtrans', [PaymentWebhookController::class, 'midtrans'])->name('webhooks.midtrans');
        Route::post('/doku',     [PaymentWebhookController::class, 'doku'])->name('webhooks.doku');
    });

    // -------------------------------------------------------------------------
    // Authenticated endpoints
    // -------------------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function () {

        // Orders
        Route::get('/orders',                 [OrderController::class, 'index']);
        Route::post('/orders',                [OrderController::class, 'store']);
        Route::get('/orders/{order}',         [OrderController::class, 'show']);
        Route::post('/orders/{order}/submit', [OrderController::class, 'submit']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

        // Order Items
        Route::post('/orders/{order}/items',                      [OrderItemController::class, 'store']);
        Route::patch('/orders/{order}/items/{orderItem}',         [OrderItemController::class, 'update']);
        Route::delete('/orders/{order}/items/{orderItem}',        [OrderItemController::class, 'destroy']);

        // Order Status
        Route::get('/orders/{order}/status',   [OrderStatusController::class, 'show']);
        Route::get('/orders/{order}/timeline', [OrderStatusController::class, 'timeline']);

        // Payments
        Route::get('/orders/{order}/payments',                         [PaymentController::class, 'index']);
        Route::post('/orders/{order}/payments',                        [PaymentController::class, 'store']);
        Route::get('/payments/{payment}',                              [PaymentController::class, 'show']);
        Route::patch('/orders/{order}/payments/{payment}/confirm',     [PaymentController::class, 'confirm'])->name('payments.confirm');

        // QRIS — Generate dynamic QR code
        Route::get('/qris/status',            [QrisController::class, 'status']);
        Route::post('/qris/generate',          [QrisController::class, 'generate']);
        Route::post('/orders/{order}/qris',    [QrisController::class, 'generateForOrder']);

        // Loyalty
        Route::prefix('customers/{customer}/loyalty')->group(function () {
            Route::get('/summary',      [CustomerLoyaltyController::class, 'summary']);
            Route::get('/challenges',   [CustomerLoyaltyController::class, 'challenges']);
            Route::get('/transactions', [CustomerLoyaltyController::class, 'transactions']);
            Route::get('/rewards',      [CustomerLoyaltyController::class, 'rewards']);
        });

        // Promotions & Gift Cards
        Route::post('/promotions/validate', [PromotionController::class, 'validatePromo']);
        Route::post('/giftcards/validate',  [GiftCardController::class, 'validateGiftCard']);
    });
});
