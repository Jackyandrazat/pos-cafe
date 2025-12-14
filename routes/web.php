<?php

use App\Http\Controllers\AdminAccessCodeController;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::controller(AdminAccessCodeController::class)
    ->prefix('admin')
    ->name('admin.access-code.')
    ->group(function () {
        Route::get('access-code', 'show')->name('show');
        Route::post('access-code', 'store')->name('store');
    });

