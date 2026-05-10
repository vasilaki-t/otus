<label>
    Название
    <input name="name" value="{{ old('name', $messenger->name) }}" required>
    @error('name') <span class="error">{{ $message }}</span> @enderror
</label>
