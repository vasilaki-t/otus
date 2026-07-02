<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PageApiController;
use Illuminate\Support\Facades\Route;

// Public: obtain a personal access token.
Route::post('login', [AuthController::class, 'login'])->name('api.login');

// Protected: require a valid Sanctum bearer token.
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('user', [AuthController::class, 'me'])->name('api.user');
    Route::get('me', [AuthController::class, 'me'])->name('api.me');

    Route::apiResource('pages', PageApiController::class);
});
