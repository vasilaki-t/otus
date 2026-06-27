<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MessengerRequest;
use App\Models\Messenger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessengerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $messengers = Messenger::query()
            ->withCount('requestHistories')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.messengers.index', compact('messengers', 'search'));
    }

    public function create(): View
    {
        return view('admin.messengers.create', [
            'messenger' => new Messenger,
        ]);
    }

    public function store(MessengerRequest $request): RedirectResponse
    {
        Messenger::query()->create($request->validated());

        return redirect()
            ->route('admin.messengers.index')
            ->with('status', 'Мессенджер создан.');
    }

    public function edit(Messenger $messenger): View
    {
        return view('admin.messengers.edit', compact('messenger'));
    }

    public function update(MessengerRequest $request, Messenger $messenger): RedirectResponse
    {
        $messenger->update($request->validated());

        return redirect()
            ->route('admin.messengers.index')
            ->with('status', 'Мессенджер обновлен.');
    }

    public function destroy(Messenger $messenger): RedirectResponse
    {
        $messenger->delete();

        return redirect()
            ->route('admin.messengers.index')
            ->with('status', 'Мессенджер удален.');
    }
}
