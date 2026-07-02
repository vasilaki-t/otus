@extends('admin.layout')

@section('title', 'История запросов')

@section('content')
    <div class="topbar">
        <h1>История запросов</h1>
        <a class="btn" href="{{ route('admin.request-histories.create') }}">Добавить запись</a>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="toolbar">
                <form class="search" method="GET" action="{{ route('admin.request-histories.index') }}">
                    <input name="search" value="{{ $search }}" placeholder="Поиск по тексту запроса или ответа">
                    <button class="secondary" type="submit">Найти</button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Диалог</th>
                        <th>Мессенджер</th>
                        <th>Запрос</th>
                        <th>Ответ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requestHistories as $requestHistory)
                        <tr>
                            <td>
                                #{{ $requestHistory->dialog_id }}
                                <div class="hint">{{ $requestHistory->dialog?->user?->username }}</div>
                            </td>
                            <td><span class="badge">{{ $requestHistory->messenger?->name }}</span></td>
                            <td>{{ $requestHistory->request_preview }}</td>
                            <td>
                                {{ $requestHistory->response_text ? str($requestHistory->response_text)->limit(90) : 'Нет ответа' }}
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="btn secondary" href="{{ route('admin.request-histories.edit', $requestHistory) }}">Редактировать</a>
                                    <form method="POST" action="{{ route('admin.request-histories.destroy', $requestHistory) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit" onclick="return confirm('Удалить запись истории?')">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="5">История пока пустая.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pagination">{{ $requestHistories->links() }}</div>
        </div>
    </div>
@endsection
