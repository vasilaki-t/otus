@extends('admin.layout')

@section('title', 'Дашборд')

@section('content')
    <div class="topbar">
        <h1>Дашборд</h1>
    </div>

    <div class="cards">
        <a class="card" href="{{ route('admin.pages.index') }}">
            <div class="card-value">{{ $stats['pages'] }}</div>
            <div class="card-label">Страницы</div>
            <div class="hint">Опубликовано: {{ $stats['published_pages'] }}</div>
        </a>

        <a class="card" href="{{ route('admin.messengers.index') }}">
            <div class="card-value">{{ $stats['messengers'] }}</div>
            <div class="card-label">Мессенджеры</div>
        </a>

        <a class="card" href="{{ route('admin.request-histories.index') }}">
            <div class="card-value">{{ $stats['request_histories'] }}</div>
            <div class="card-label">История запросов</div>
        </a>

        <div class="card">
            <div class="card-value">{{ $stats['dialogs'] }}</div>
            <div class="card-label">Диалоги</div>
        </div>

        <div class="card">
            <div class="card-value">{{ $stats['users'] }}</div>
            <div class="card-label">Пользователи</div>
        </div>
    </div>
@endsection
