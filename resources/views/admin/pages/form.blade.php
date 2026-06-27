<div class="two">
    <label>
        Название
        <input name="title" value="{{ old('title', $page->title) }}" required>
        @error('title') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        Slug
        <input name="slug" value="{{ old('slug', $page->slug) }}" placeholder="zapolnitsya-avtomaticheski">
        @error('slug') <span class="error">{{ $message }}</span> @enderror
    </label>
</div>

<label>
    Контент
    <textarea name="content">{{ old('content', $page->content) }}</textarea>
    @error('content') <span class="error">{{ $message }}</span> @enderror
</label>

<div class="two">
    <label class="checkbox">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))>
        Опубликована
        @error('is_published') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        Дата публикации
        <input type="datetime-local" name="published_at" value="{{ old('published_at', $page->published_at?->format('Y-m-d\TH:i')) }}">
        @error('published_at') <span class="error">{{ $message }}</span> @enderror
    </label>
</div>
