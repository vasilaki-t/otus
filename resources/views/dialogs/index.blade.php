@extends('layouts.app')

@section('title', 'Мои диалоги')

@section('content')
    <h1>Мои диалоги</h1>

    <p><a href="{{ route('dialogs.create') }}">Создать диалог</a></p>

    @if ($dialogs->isEmpty())
        <p>У вас пока нет диалогов.</p>
    @else
        <table border="1" cellpadding="6">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Запросов</th>
                    <th>Создан</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dialogs as $dialog)
                    <tr>
                        <td>{{ $dialog->id }}</td>
                        <td>{{ $dialog->title }}</td>
                        <td>{{ $dialog->request_histories_count }}</td>
                        <td>{{ $dialog->created_at?->format('d.m.Y H:i') }}</td>
                        <td>
                            <a href="{{ route('dialogs.edit', $dialog) }}">Редактировать</a>
                            <form method="POST" action="{{ route('dialogs.destroy', $dialog) }}" style="display:inline"
                                  onsubmit="return confirm('Удалить диалог?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $dialogs->links() }}
    @endif
@endsection
