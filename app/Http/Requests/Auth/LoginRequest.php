<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation.
     * Supports both C# ShopFlower field names (TenDangNhap, MatKhau) and Laravel field names (email, password).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('TenDangNhap') && ! $this->has('email')) {
            $this->merge(['email' => $this->input('TenDangNhap')]);
        }
        if ($this->has('MatKhau') && ! $this->has('password')) {
            $this->merge(['password' => $this->input('MatKhau')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     * Supports authentication by either email or username (TenDangNhap).
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginInput = (string) $this->input('email');
        $password = (string) $this->input('password');

        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $credentials = $isEmail
            ? ['email' => $loginInput, 'password' => $password]
            : ['username' => $loginInput, 'password' => $password];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            // If attempt by username failed, try attempt by email as fallback
            if (! $isEmail && Auth::attempt(['email' => $loginInput, 'password' => $password], $this->boolean('remember'))) {
                RateLimiter::clear($this->throttleKey());
                return;
            }

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
