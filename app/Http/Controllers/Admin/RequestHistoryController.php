<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RequestHistoryRequest;
use App\Models\Dialog;
use App\Models\Messenger;
use App\Models\RequestHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $requestHistories = RequestHistory::query()
            ->with(['dialog.user', 'messenger'])
            ->when($search !== '', fn ($query) => $query
                ->where('request_text', 'like', "%{$search}%")
                ->orWhere('response_text', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.request-histories.index', compact('requestHistories', 'search'));
    }

    public function create(): View
    {
        return view('admin.request-histories.create', [
            'requestHistory' => new RequestHistory,
            ...$this->formData(),
        ]);
    }

    public function store(RequestHistoryRequest $request): RedirectResponse
    {
        RequestHistory::query()->create($request->validated());

        return redirect()
            ->route('admin.request-histories.index')
            ->with('status', 'Запись истории создана.');
    }

    public function edit(RequestHistory $requestHistory): View
    {
        return view('admin.request-histories.edit', [
            'requestHistory' => $requestHistory,
            ...$this->formData(),
        ]);
    }

    public function update(RequestHistoryRequest $request, RequestHistory $requestHistory): RedirectResponse
    {
        $requestHistory->update($request->validated());

        return redirect()
            ->route('admin.request-histories.index')
            ->with('status', 'Запись истории обновлена.');
    }

    public function destroy(RequestHistory $requestHistory): RedirectResponse
    {
        $requestHistory->delete();

        return redirect()
            ->route('admin.request-histories.index')
            ->with('status', 'Запись истории удалена.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'dialogs' => Dialog::query()
                ->with('user')
                ->latest()
                ->get(),
            'messengers' => Messenger::query()
                ->orderBy('name')
                ->get(),
        ];
    }
}
