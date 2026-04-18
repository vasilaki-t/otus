<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home', ['title' => 'Главная']);
})->name('home');

Route::get('/user', function () {
    return view('pages.user', ['title' => 'Страница пользователя']);
})->name('user');

Route::get('/register', function () {
    return view('pages.register', ['title' => 'Регистрация']);
})->name('register');

Route::get('/about', function () {
    return view('pages.about', ['title' => 'О проекте']);
})->name('about');
