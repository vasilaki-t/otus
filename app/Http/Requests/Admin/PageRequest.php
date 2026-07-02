<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize slug and publication fields before validation.
     */
    protected function prepareForValidation(): void
    {
        $slug = $this->filled('slug')
            ? Str::slug((string) $this->input('slug'))
            : Str::slug((string) $this->input('title'));

        $isPublished = $this->boolean('is_published');

        $publishedAt = $this->input('published_at');
        if ($isPublished && empty($publishedAt)) {
            $publishedAt = now()->toDateTimeString();
        }

        $this->merge([
            'slug' => $slug,
            'is_published' => $isPublished,
            'published_at' => $publishedAt ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique('pages', 'slug')->ignore($this->route('page')),
            ],
            'content' => ['nullable', 'string'],
            'is_published' => ['required', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
