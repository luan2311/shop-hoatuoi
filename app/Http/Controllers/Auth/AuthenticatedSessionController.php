<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->user() && $request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->user() && (
            strtolower((string) $request->user()->username) === 'user_demo'
            || strtolower((string) $request->user()->email) === 'user@shophoatuoi.com'
        )) {
            if ($request->filled('returnUrl')) {
                $returnUrl = $request->input('returnUrl');
                if (! str_starts_with($returnUrl, 'http://') && ! str_starts_with($returnUrl, 'https://') && ! str_contains($returnUrl, 'dashboard')) {
                    return redirect($returnUrl);
                }
            }

            session()->forget('url.intended');

            return redirect()->route('home');
        }

        if ($request->filled('returnUrl')) {
            $returnUrl = $request->input('returnUrl');
            if (! str_starts_with($returnUrl, 'http://') && ! str_starts_with($returnUrl, 'https://')) {
                return redirect($returnUrl);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
