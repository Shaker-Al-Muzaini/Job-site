<?php

use App\Http\Controllers\LoginApiController;
use App\Presentation\Http\Controllers\Page\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('Auth:sanctum');

// Login   routes
Route::post('login', [LoginApiController::class,'login']);

// Page domain routes
Route::apiResource('pages', PageController::class);
