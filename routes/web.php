<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('/index', function () {
    return Inertia::render('Auth/Index');
});

Route::get('/register', function () {
    return Inertia::render('Auth/Register');
});
//
Route::post('/register', RegisterController::class);

Route::get('/profile', function () {
    return Inertia::render('Auth/Profile');
});
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
});
Route::get('/profile', [RegisterController::class, 'showProfile'])->name('profile');

Route::post('/login', LoginController::class);
