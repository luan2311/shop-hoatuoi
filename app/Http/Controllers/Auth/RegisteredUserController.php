<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * Ported from C# AccountController Dang_ky action.
     * Supports both C# ShopFlower field names (TenDangNhap, Email, MatKhau, XacNhanMatKhau) and Laravel standard field names.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Support both ShopFlower Vietnamese field names and Laravel English names
        if ($request->has('TenDangNhap') && ! $request->has('name')) {
            $request->merge(['name' => $request->input('TenDangNhap')]);
        }
        if ($request->has('Email') && ! $request->has('email')) {
            $request->merge(['email' => $request->input('Email')]);
        }
        if ($request->has('MatKhau') && ! $request->has('password')) {
            $request->merge(['password' => $request->input('MatKhau')]);
        }
        if ($request->has('XacNhanMatKhau') && ! $request->has('password_confirmation')) {
            $request->merge(['password_confirmation' => $request->input('XacNhanMatKhau')]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $displayName = $request->input('TenHienThi') ?: $request->input('name');
        $username = $request->input('TenDangNhap') ?: $request->input('name');

        $user = User::create([
            'name' => $displayName,
            'username' => $username,
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
