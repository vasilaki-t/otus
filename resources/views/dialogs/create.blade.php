@extends('layouts.app')

@section('title', 'Новый диалог')

@section('content')
    <h1>Новый диалог</h1>

    <form method="POST" action="{{ route('dialogs.store') }}">
        @csrf

        <div>
            <label for="title">Название</label>
            <input id="title" type="text" name="title" value="{{ old('title') }}" required autofocus>
            @error('title')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">Создать</button>
        <a href="{{ route('dialogs.index') }}">Отмена</a>
    </form>
@endsection
