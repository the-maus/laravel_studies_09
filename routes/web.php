<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

// non authenticated users only
Route::middleware('guest')->group(function(){
    // login routes
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');

    // registration routes
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'storeUser'])->name('store_user');

    // new user confirmation
    Route::get('/new_user_confirmation/{token}', [AuthController::class, 'newUserConfirmation'])->name('new_user_confirmation');

    // forgot password
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot_password');
    Route::post('/forgot-password', [AuthController::class, 'sendResetPasswordLink'])->name('send_reset_password_link');

    // reset password
    Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('reset_password');
    Route::post('/reset-password', [AuthController::class, 'resetPasswordUpdate'])->name('reset_password_update');
});

// authenticated users only
Route::middleware('auth')->group(function(){
    Route::get('/', [MainController::class, 'home'])->name('home');
    
    // profile - change password
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'changePassword'])->name('change_password');

    // logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});