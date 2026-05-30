<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = trim($this->email);

        // Email login
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            if (! Auth::attempt(['email' => $login, 'password' => $this->password], $this->remember)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'form.email' => trans('auth.failed'),
                ]);
            }

            RateLimiter::clear($this->throttleKey());
            return;
        }

        // Mobile login
        $mobile = preg_replace('/\s+/', '', $login);

        $user = User::where('mobile', $mobile)->first();
        if (! $user) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        if (! Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return strtolower($this->email) . '|' . request()->ip();
    }
}
