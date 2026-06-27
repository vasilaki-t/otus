<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = User::query()->create($request->validated());

        $userRole = Role::query()->where('slug', Role::USER)->first();
        if ($userRole !== null) {
            $user->roles()->syncWithoutDetaching([$userRole->id]);
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('dialogs.index'));
    }
}
