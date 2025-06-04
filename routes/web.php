<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Filament\Notifications\Notification;

Route::get('/', function () {
    return view('welcome');
});

