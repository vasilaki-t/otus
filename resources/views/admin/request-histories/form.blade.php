<div class="two">
    <label>
        Диалог
        <select name="dialog_id" required>
            <option value="">Выберите диалог</option>
            @foreach ($dialogs as $dialog)
                <option value="{{ $dialog->id }}" @selected((int) old('dialog_id', $requestHistory->dialog_id) === $dialog->id)>
                    #{{ $dialog->id }} - {{ $dialog->user?->username }}
                </option>
            @endforeach
        </select>
        @error('dialog_id') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        Мессенджер
        <select name="messenger_id" required>
            <option value="">Выберите мессенджер</option>
            @foreach ($messengers as $messenger)
                <option value="{{ $messenger->id }}" @selected((int) old('messenger_id', $requestHistory->messenger_id) === $messenger->id)>
                    {{ $messenger->name }}
                </option>
            @endforeach
        </select>
        @error('messenger_id') <span class="error">{{ $message }}</span> @enderror
    </label>
</div>

<label>
    Запрос
    <textarea name="request_text" required>{{ old('request_text', $requestHistory->request_text) }}</textarea>
    @error('request_text') <span class="error">{{ $message }}</span> @enderror
</label>

<label>
    Ответ
    <textarea name="response_text">{{ old('response_text', $requestHistory->response_text) }}</textarea>
    @error('response_text') <span class="error">{{ $message }}</span> @enderror
</label>
