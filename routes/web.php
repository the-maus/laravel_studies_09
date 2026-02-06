<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// non authenticated users only
Route::middleware('guest')->group(function(){
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
});

// authenticated users only
Route::middleware('auth')->group(function(){
    Route::get('/', function(){
        echo 'Hello, World!';
    })->name('home');
});