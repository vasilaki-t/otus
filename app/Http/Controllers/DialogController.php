<?php

namespace App\Http\Controllers;

use App\Events\DialogCreated;
use App\Http\Requests\DialogRequest;
use App\Models\Dialog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DialogController extends Controller
{
    public function index(Request $request): View
    {
        $dialogs = $request->user()
            ->dialogs()
            ->withCount('requestHistories')
            ->latest()
            ->paginate(10);

        return view('dialogs.index', compact('dialogs'));
    }

    public function create(): View
    {
        $this->authorize('create', Dialog::class);

        return view('dialogs.create', [
            'dialog' => new Dialog,
        ]);
    }

    public function store(DialogRequest $request): RedirectResponse
    {
        $this->authorize('create', Dialog::class);

        $dialog = $request->user()->dialogs()->create($request->validated());

        // Publish the domain event; queued listeners react in the background.
        DialogCreated::dispatch($dialog);

        return redirect()
            ->route('dialogs.index')
            ->with('status', 'Диалог создан.');
    }

    public function edit(Dialog $dialog): View
    {
        $this->authorize('update', $dialog);

        return view('dialogs.edit', compact('dialog'));
    }

    public function update(DialogRequest $request, Dialog $dialog): RedirectResponse
    {
        $this->authorize('update', $dialog);

        $dialog->update($request->validated());

        return redirect()
            ->route('dialogs.index')
            ->with('status', 'Диалог обновлён.');
    }

    public function destroy(Dialog $dialog): RedirectResponse
    {
        $this->authorize('delete', $dialog);

        $dialog->delete();

        return redirect()
            ->route('dialogs.index')
            ->with('status', 'Диалог удалён.');
    }
}
