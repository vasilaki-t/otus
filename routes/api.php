<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PageApiController;
use App\Http\Controllers\Api\V1\DialogApiController;
use Illuminate\Support\Facades\Route;

// Public: obtain a personal access token.
Route::post('login', [AuthController::class, 'login'])->name('api.login');

// Protected: require a valid Sanctum bearer token (HW-19 API).
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('user', [AuthController::class, 'me'])->name('api.user');
    Route::get('me', [AuthController::class, 'me'])->name('api.me');

    Route::apiResource('pages', PageApiController::class);
});

// Versioned API (v1) for external systems, authenticated via Laravel Passport
// (OAuth2). Tokens are issued by POST /oauth/token (password grant) or by the
// client_credentials grant; clients send "Authorization: Bearer <token>".
Route::prefix('v1')
    ->name('api.v1.')
    ->middleware('auth:api')
    ->group(function (): void {
        Route::apiResource('dialogs', DialogApiController::class);
    });
