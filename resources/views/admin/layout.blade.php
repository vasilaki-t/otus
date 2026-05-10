<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Админка') - {{ config('app.name', 'Laravel') }}</title>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">Админка</div>
            <nav class="nav">
                <a @class(['active' => request()->routeIs('admin.pages.*')]) href="{{ route('admin.pages.index') }}">Страницы</a>
                <a @class(['active' => request()->routeIs('admin.messengers.*')]) href="{{ route('admin.messengers.index') }}">Мессенджеры</a>
                <a @class(['active' => request()->routeIs('admin.request-histories.*')]) href="{{ route('admin.request-histories.index') }}">История</a>
            </nav>
        </aside>
        <main class="content">
            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
