<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PageApiRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PageApiController extends Controller
{
    /**
     * Paginated list of pages.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->string('search')->toString();

        $pages = Page::query()
            ->when($search !== '', fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return PageResource::collection($pages);
    }

    /**
     * Display a single page.
     */
    public function show(Page $page): PageResource
    {
        return new PageResource($page);
    }

    /**
     * Create a new page.
     */
    public function store(PageApiRequest $request): JsonResponse
    {
        $page = Page::query()->create($request->validated());

        return (new PageResource($page))
            ->additional(['message' => 'Страница создана.'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update an existing page.
     */
    public function update(PageApiRequest $request, Page $page): PageResource
    {
        $page->update($request->validated());

        return new PageResource($page->refresh());
    }

    /**
     * Delete a page.
     */
    public function destroy(Page $page): Response
    {
        $page->delete();

        return response()->noContent();
    }
}
