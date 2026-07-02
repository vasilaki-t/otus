@extends('layouts.app')

@section('title', 'Редактирование диалога')

@section('content')
    <h1>Редактирование диалога #{{ $dialog->id }}</h1>

    <form method="POST" action="{{ route('dialogs.update', $dialog) }}">
        @csrf
        @method('PUT')

        <div>
            <label for="title">Название</label>
            <input id="title" type="text" name="title" value="{{ old('title', $dialog->title) }}" required autofocus>
            @error('title')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">Сохранить</button>
        <a href="{{ route('dialogs.index') }}">Отмена</a>
    </form>
@endsection
