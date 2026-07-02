@extends('layouts.app')

@section('title', 'Вход')

@section('content')
    <h1>Вход</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="password">Пароль</label>
            <input id="password" type="password" name="password" required>
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label>
                <input type="checkbox" name="remember"> Запомнить меня
            </label>
        </div>

        <button type="submit">Войти</button>
    </form>

    <p><a href="{{ route('register') }}">Регистрация</a></p>
@endsection
