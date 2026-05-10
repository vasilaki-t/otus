@extends('admin.layout')

@section('title', 'Мессенджеры')

@section('content')
    <div class="topbar">
        <h1>Мессенджеры</h1>
        <a class="btn" href="{{ route('admin.messengers.create') }}">Добавить мессенджер</a>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="toolbar">
                <form class="search" method="GET" action="{{ route('admin.messengers.index') }}">
                    <input name="search" value="{{ $search }}" placeholder="Поиск по названию">
                    <button class="secondary" type="submit">Найти</button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Записей истории</th>
                        <th>Создан</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messengers as $messenger)
                        <tr>
                            <td>{{ $messenger->name }}</td>
                            <td>{{ $messenger->request_histories_count }}</td>
                            <td>{{ $messenger->created_at?->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn secondary" href="{{ route('admin.messengers.edit', $messenger) }}">Редактировать</a>
                                    <form method="POST" action="{{ route('admin.messengers.destroy', $messenger) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit" onclick="return confirm('Удалить мессенджер и связанную историю?')">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="4">Мессенджеров пока нет.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pagination">{{ $messengers->links() }}</div>
        </div>
    </div>
@endsection
