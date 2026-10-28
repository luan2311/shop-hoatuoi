<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        if ($request->has('OldPassword') && ! $request->has('current_password')) {
            $request->merge(['current_password' => $request->input('OldPassword')]);
        }
        if ($request->has('NewPassword') && ! $request->has('password')) {
            $request->merge(['password' => $request->input('NewPassword')]);
        }
        if ($request->has('ConfirmPassword') && ! $request->has('password_confirmation')) {
            $request->merge(['password_confirmation' => $request->input('ConfirmPassword')]);
        }

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
