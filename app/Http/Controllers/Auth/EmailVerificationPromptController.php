<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $defaultRoute = $request->user()?->isAdmin() ? route('admin.dashboard') : route('home', absolute: false);

        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended($defaultRoute)
                    : view('auth.verify-email');
    }
}
