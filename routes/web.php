<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MessengerController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\RequestHistoryController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DialogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dialogs.index')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::resource('dialogs', DialogController::class)->except('show');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('pages', PageController::class)->except('show');
        Route::post('pages/{page}/toggle', [PageController::class, 'toggle'])->name('pages.toggle');
        Route::resource('messengers', MessengerController::class)->except('show');
        Route::resource('request-histories', RequestHistoryController::class)
            ->except('show')
            ->parameters(['request-histories' => 'requestHistory']);
    });
