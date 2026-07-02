@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
    <h1>Регистрация</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <label for="username">Имя пользователя</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus>
            @error('username')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
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
            <label for="password_confirmation">Повторите пароль</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>
        </div>

        <button type="submit">Зарегистрироваться</button>
    </form>

    <p><a href="{{ route('login') }}">Уже есть аккаунт? Войти</a></p>
@endsection
