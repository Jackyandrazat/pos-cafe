<?php

use App\Http\Controllers\Api\V1\Auth\GuestAuthController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\OrderItemController;
use App\Http\Controllers\Api\V1\OrderStatusController;
use App\Http\Controllers\Api\V1\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public endpoints
    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/menus/{product}', [MenuController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);

    Route::post('/auth/guest', [GuestAuthController::class, 'store']);

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

        Route::get('/orders/{order}/payments', [PaymentController::class, 'index']);
        Route::post('/orders/{order}/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    });
});
