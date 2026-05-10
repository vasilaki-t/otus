@extends('admin.layout')

@section('title', 'Редактирование истории')

@section('content')
    <div class="topbar">
        <h1>Редактирование истории</h1>
        <a class="btn secondary" href="{{ route('admin.request-histories.index') }}">Назад</a>
    </div>

    <div class="panel">
        <div class="panel-body">
            <form class="form-grid" method="POST" action="{{ route('admin.request-histories.update', $requestHistory) }}">
                @csrf
                @method('PUT')
                @include('admin.request-histories.form')
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
