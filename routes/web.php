<?php

use App\Http\Controllers\AdminAccessCodeController;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect()->to('/admin/login');
})->name('login');

Route::controller(AdminAccessCodeController::class)
    ->prefix('admin')
    ->name('admin.access-code.')
    ->group(function () {
        Route::get('access-code', 'show')->name('show');
        Route::post('access-code', 'store')->name('store');
    });

// Thermal Receipt & Kitchen Docket Printing
Route::middleware(['auth'])->prefix('admin/orders')->name('orders.')->group(function () {
    Route::get('{order}/print', [\App\Http\Controllers\PrintReceiptController::class, 'printCustomer'])->name('print.customer');
    Route::get('{order}/print-kitchen', [\App\Http\Controllers\PrintReceiptController::class, 'printKitchen'])->name('print.kitchen');
});

Route::middleware(['auth'])->prefix('admin/payments')->name('payments.')->group(function () {
    Route::get('{payment}/print', [\App\Http\Controllers\PrintReceiptController::class, 'printPayment'])->name('print');
});

// Table QR Code Generation & Printing
Route::middleware(['auth'])->prefix('admin/tables')->name('tables.qr.')->group(function () {
    Route::get('print-all', [\App\Http\Controllers\TableQrController::class, 'printAll'])->name('print-all');
    Route::get('{table}/print', [\App\Http\Controllers\TableQrController::class, 'printSingle'])->name('print');
    Route::get('{table}/download', [\App\Http\Controllers\TableQrController::class, 'downloadSvg'])->name('download');
});


