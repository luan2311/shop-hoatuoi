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

        if ($request->user() && ! $request->user()->isActive()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên để biết thêm chi tiết.',
            ]);
        }

        if ($request->user() && $request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $target = $request->input('returnUrl');
        if ($target === null) {
            $target = $request->session()->pull('url.intended');
            $origin = $request->root();
            if (is_string($target) && str_starts_with($target, $origin.'/')) {
                $target = substr($target, strlen($origin));
            }
        }
        $request->session()->forget('url.intended');

        $path = $this->safeLocalPath($target);
        if ($path !== null && parse_url($path, PHP_URL_PATH) === '/dashboard') {
            $path = null;
        }

        return redirect($path ?? route('home', absolute: false));
    }

    private function safeLocalPath(mixed $target): ?string
    {
        if (! is_string($target) || $target === '') {
            return null;
        }

        $decoded = $target;
        for ($i = 0; $i < 8; $i++) {
            $next = rawurldecode($decoded);
            if ($next === $decoded) {
                break;
            }
            $decoded = $next;
        }

        if (str_contains($decoded, '%') || preg_match('/[\\\\\x00-\x20\x7f]/', $decoded)
            || ! str_starts_with($decoded, '/') || str_starts_with($decoded, '//')) {
            return null;
        }

        $parts = parse_url($decoded);
        if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
            return null;
        }

        return $target;
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
