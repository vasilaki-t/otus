@extends('admin.layout')

@section('title', 'Редактирование мессенджера')

@section('content')
    <div class="topbar">
        <h1>Редактирование мессенджера</h1>
        <a class="btn secondary" href="{{ route('admin.messengers.index') }}">Назад</a>
    </div>

    <div class="panel">
        <div class="panel-body">
            <form class="form-grid" method="POST" action="{{ route('admin.messengers.update', $messenger) }}">
                @csrf
                @method('PUT')
                @include('admin.messengers.form')
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
