<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DubController;
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


Route::get('/healthz', function () {
    return 'OK';
});

Route::get('/dub', [DubController::class, 'index']);
Route::post('/dub/step1', [DubController::class, 'step1Download'])->name('dub.step1');
Route::post('/dub/step2', [DubController::class, 'step2Audio'])->name('dub.step2');
Route::post('/dub/step3', [DubController::class, 'step3Whisper'])->name('dub.step3');
Route::post('/dub/step4', [DubController::class, 'step4TTS'])->name('dub.step4');
Route::post('/dub/step5', [DubController::class, 'step5Merge'])->name('dub.step5');
