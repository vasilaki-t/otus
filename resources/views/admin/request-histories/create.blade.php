@extends('admin.layout')

@section('title', 'Новая запись истории')

@section('content')
    <div class="topbar">
        <h1>Новая запись истории</h1>
        <a class="btn secondary" href="{{ route('admin.request-histories.index') }}">Назад</a>
    </div>

    <div class="panel">
        <div class="panel-body">
            <form class="form-grid" method="POST" action="{{ route('admin.request-histories.store') }}">
                @csrf
                @include('admin.request-histories.form')
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
