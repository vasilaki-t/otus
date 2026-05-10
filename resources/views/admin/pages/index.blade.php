@extends('admin.layout')

@section('title', 'Страницы')

@section('content')
    <div class="topbar">
        <h1>Страницы</h1>
        <a class="btn" href="{{ route('admin.pages.create') }}">Создать страницу</a>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="toolbar">
                <form class="search" method="GET" action="{{ route('admin.pages.index') }}">
                    <input name="search" value="{{ $search }}" placeholder="Поиск по названию или slug">
                    <button class="secondary" type="submit">Найти</button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Статус</th>
                        <th>Обновлена</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                        <tr>
                            <td>{{ $page->title }}</td>
                            <td><span class="badge">{{ $page->slug }}</span></td>
                            <td>
                                <span @class(['badge', 'live' => $page->is_published])>
                                    {{ $page->is_published ? 'Опубликована' : 'Черновик' }}
                                </span>
                            </td>
                            <td>{{ $page->updated_at?->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn secondary" href="{{ route('admin.pages.edit', $page) }}">Редактировать</a>
                                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit" onclick="return confirm('Удалить страницу?')">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="5">Страниц пока нет.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pagination">{{ $pages->links() }}</div>
        </div>
    </div>
@endsection
