<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Кабинет') - {{ config('app.name', 'Laravel') }}</title>
</head>
<body>
    <header class="topbar">
        <nav class="nav">
            <a href="{{ route('dialogs.index') }}">Мои диалоги</a>
            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Админка</a>
                @endif
            @endauth
        </nav>

        @auth
            <span class="user">{{ auth()->user()->username }}</span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit">Выйти</button>
            </form>
        @endauth
    </header>

    <main class="content">
        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
