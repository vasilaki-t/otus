@extends('admin.layout')

@section('title', 'Новая страница')

@section('content')
    <div class="topbar">
        <h1>Новая страница</h1>
        <a class="btn secondary" href="{{ route('admin.pages.index') }}">Назад</a>
    </div>

    <div class="panel">
        <div class="panel-body">
            <form class="form-grid" method="POST" action="{{ route('admin.pages.store') }}">
                @csrf
                @include('admin.pages.form')
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
