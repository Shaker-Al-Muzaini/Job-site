<?php

use App\Presentation\Http\Controllers\Page\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Page domain routes

// Page domain routes
Route::apiResource('pages', PageController::class);
