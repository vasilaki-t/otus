@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="mb-4">Регистрация</h1>

                        <form>
                            <div class="mb-3">
                                <label for="name" class="form-label">Имя</label>
                                <input type="text" class="form-control" id="name" placeholder="Введите имя">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" placeholder="name@example.com">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль</label>
                                <input type="password" class="form-control" id="password" placeholder="Минимум 8 символов">
                            </div>
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Подтвердите пароль</label>
                                <input type="password" class="form-control" id="password_confirmation" placeholder="Повторите пароль">
                            </div>
                            <button type="button" class="btn btn-primary w-100">Зарегистрироваться</button>
                        </form>
                        <p class="text-muted mt-3 mb-0">Это только прототип формы без настоящей регистрации.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
