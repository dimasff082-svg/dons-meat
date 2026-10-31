<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            return redirect()->to($this->redirectPathForUser($user));
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        return match ($user->role) {
            UserRole::Cashier => redirect()->route('admin.direct-sales.create'),
            UserRole::Owner => redirect()->route('admin.reports.sales'),
            default => redirect()->intended(route('admin.dashboard')),
        };
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'Anda telah berhasil logout.');
    }

    protected function redirectPathForUser(User $user): string
    {
        return match ($user->role) {
            UserRole::Cashier => route('admin.direct-sales.create'),
            UserRole::Owner => route('admin.reports.sales'),
            default => route('admin.dashboard'),
        };
    }
}
