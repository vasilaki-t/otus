@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="p-4 mb-4 bg-white border rounded">
            <h1 class="mb-3">Главная страница</h1>
            <p class="mb-3">
                Это главная страница учебного сайта на Laravel. Здесь можно разместить основную информацию о проекте.
            </p>
            <a href="{{ route('register') }}" class="btn btn-primary">Перейти к регистрации</a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Laravel</h5>
                        <p class="card-text">Проект создан на Laravel и использует Blade шаблоны.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Bootstrap</h5>
                        <p class="card-text">Для оформления страниц подключен Bootstrap.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Страницы</h5>
                        <p class="card-text">На сайте есть главная, пользователь, регистрация и статическая страница.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
