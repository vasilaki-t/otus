@extends('admin.layout')

@section('title', 'Новый мессенджер')

@section('content')
    <div class="topbar">
        <h1>Новый мессенджер</h1>
        <a class="btn secondary" href="{{ route('admin.messengers.index') }}">Назад</a>
    </div>

    <div class="panel">
        <div class="panel-body">
            <form class="form-grid" method="POST" action="{{ route('admin.messengers.store') }}">
                @csrf
                @include('admin.messengers.form')
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
