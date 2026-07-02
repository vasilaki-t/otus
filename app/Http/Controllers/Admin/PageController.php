<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    public function store(PageRequest $request): RedirectResponse
    {
        Page::query()->create($request->validated());

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Страница создана.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $page->update($request->validated());

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

    public function toggle(Page $page): RedirectResponse
    {
        $page->is_published = ! $page->is_published;

        if ($page->is_published && $page->published_at === null) {
            $page->published_at = now();
        }

        $page->save();

        return redirect()
            ->route('admin.pages.index')
            ->with('status', $page->is_published
                ? 'Страница опубликована.'
                : 'Страница снята с публикации.');
    }
}
