<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Auth routes (placeholders, implemented in M1.2)
|--------------------------------------------------------------------------
*/
Route::get('/login', fn () => 'TODO: Auth/Login.vue')->name('login');
Route::get('/register', fn () => 'TODO: Auth/Register.vue')->name('register');
