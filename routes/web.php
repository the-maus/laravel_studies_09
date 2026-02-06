<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/',function(){
    DB::connection()->getPdo();
    echo 'Home';
});

// default (auth) laravel middleware accessible only to logged users
Route::view('/test', 'test')->middleware('auth');

Route::get('/login', function(){
    echo "Login form";
})->name('login');

// default (guest) laravel middleware accessible only to non-logged users
Route::middleware('guest')->group(function(){
    Route::get('/register', function(){
        echo "Register form";
    })->name('register');
    // can add more routes
});
Route::get('/register', function(){
        echo "Register form";
})->name('register')->middleware('guest');