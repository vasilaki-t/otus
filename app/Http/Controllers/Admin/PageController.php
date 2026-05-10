<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PageController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $pages = Page::query()
            ->when($search !== '', fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pages.index', compact('pages', 'search'));
    }

    public function create(): View
    {
        return view('admin.pages.create', [
            'page' => new Page(['is_published' => false]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        Page::query()->create($data);

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Страница создана.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page->update($this->validatedData($request, $page));

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Страница обновлена.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Страница удалена.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Page $page = null): array
    {
        $request->merge([
            'slug' => $request->filled('slug')
                ? Str::slug((string) $request->input('slug'))
                : Str::slug((string) $request->input('title')),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique('pages', 'slug')->ignore($page),
            ],
            'content' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $data['is_published'] = $request->boolean('is_published');

        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
