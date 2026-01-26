<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\GiftCardController;
use App\Http\Controllers\Api\V1\OrderItemController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\Api\V1\OrderStatusController;
use App\Http\Controllers\Api\V1\Auth\GuestAuthController;
use App\Http\Controllers\Api\V1\CustomerLoyaltyController;

Route::prefix('v1')->group(function () {
    // Public endpoints
    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/menus/{product}', [MenuController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);

    Route::post('/auth/guest', [GuestAuthController::class, 'store']);
    Route::get('/promotions', [PromotionController::class, 'index']);

    Route::middleware('auth:sanctum')->get('/auth/validate', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
        ]);
    });


    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders/{order}/submit', [OrderController::class, 'submit']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

        Route::post('/orders/{order}/items', [OrderItemController::class, 'store']);
        Route::patch('/orders/{order}/items/{orderItem}', [OrderItemController::class, 'update']);
        Route::delete('/orders/{order}/items/{orderItem}', [OrderItemController::class, 'destroy']);

        Route::get('/orders/{order}/status', [OrderStatusController::class, 'show']);
        Route::get('/orders/{order}/timeline', [OrderStatusController::class, 'timeline']);


        Route::get('/orders/{order}/payments', [PaymentController::class, 'index']);
        Route::post('/orders/{order}/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);

        Route::get('/customers/{customer}/loyalty/summary', [CustomerLoyaltyController::class, 'summary']);

        Route::post('/promotions/validate', [PromotionController::class, 'validatePromo']);
        Route::post('/giftcards/validate', [GiftCardController::class, 'validateGiftCard']);
    });
});
