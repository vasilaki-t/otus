<?php

use App\Http\Controllers\Admin\MessengerController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\RequestHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::redirect('/', '/admin/pages')->name('dashboard');
        Route::resource('pages', PageController::class)->except('show');
        Route::resource('messengers', MessengerController::class)->except('show');
        Route::resource('request-histories', RequestHistoryController::class)
            ->except('show')
            ->parameters(['request-histories' => 'requestHistory']);
    });
