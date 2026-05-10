@extends('admin.layout')

@section('title', 'Редактирование страницы')

@section('content')
    <div class="topbar">
        <h1>Редактирование страницы</h1>
        <a class="btn secondary" href="{{ route('admin.pages.index') }}">Назад</a>
    </div>

    <div class="panel">
        <div class="panel-body">
            <form class="form-grid" method="POST" action="{{ route('admin.pages.update', $page) }}">
                @csrf
                @method('PUT')
                @include('admin.pages.form')
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
