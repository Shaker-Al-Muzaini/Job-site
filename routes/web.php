<?php

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

Route::get('/profile', function () {
    return Inertia::render('Auth/Profile');
});
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
});
