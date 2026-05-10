<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Messenger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

    public function store(Request $request): RedirectResponse
    {
        Messenger::query()->create($this->validatedData($request));

        return redirect()
            ->route('admin.messengers.index')
            ->with('status', 'Мессенджер создан.');
    }

    public function edit(Messenger $messenger): View
    {
        return view('admin.messengers.edit', compact('messenger'));
    }

    public function update(Request $request, Messenger $messenger): RedirectResponse
    {
        $messenger->update($this->validatedData($request, $messenger));

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

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Messenger $messenger = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('messengers', 'name')->ignore($messenger),
            ],
        ]);
    }
}
