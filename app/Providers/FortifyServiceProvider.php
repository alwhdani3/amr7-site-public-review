<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureAuthentication();
    }

    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    private function configureViews(): void
    {
        Fortify::loginView(fn () => redirect()->to($this->homeUrlWithAuth('login')));
        Fortify::registerView(fn () => redirect()->to($this->homeUrlWithAuth('register')));

        Fortify::verifyEmailView(fn () => view('livewire.auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('livewire.auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('livewire.auth.confirm-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('livewire.auth.forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('livewire.auth.reset-password', [
            'request' => $request,
        ]));
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by((string) $request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $identity = Str::lower((string) $request->input('login', $request->input('email', '')));
            return Limit::perMinute(10)->by($identity . '|' . $request->ip());
        });

        // Phase 2 hardening — public form / invitation surface limiters.
        // Keyed per-IP because these endpoints accept anonymous traffic.

        RateLimiter::for('invitations.accept', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('public-forms', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }

    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $request->validate([
                'login_type' => ['required', 'in:email,mobile'],
                'login'      => ['required', 'string', 'max:255'],
                'password'   => ['required', 'string'],
            ], [
                'login_type.required' => 'حدد طريقة الدخول.',
                'login.required'      => 'أدخل البريد أو رقم الجوال.',
                'password.required'   => 'أدخل كلمة المرور.',
            ]);

            if ($request->filled('redirect_to')) {
                session()->put('url.intended', $request->input('redirect_to'));
            }

            $loginType = (string) $request->input('login_type');
            $login     = (string) $request->input('login');
            $password  = (string) $request->input('password');

            $user = null;

            if ($loginType === 'email') {
                $user = User::query()
                    ->where('email', Str::lower(trim($login)))
                    ->first();
            } else {
                $mobile = $this->normalizeSaudiMobile($login);

                $user = User::query()
                    ->where('mobile', $mobile)
                    ->first();
            }

            if ($user && filled($user->password) && Hash::check($password, $user->password)) {
                return $user;
            }

            return null;
        });
    }

    private function normalizeSaudiMobile(string $input): string
    {
        $mobile = preg_replace('/\D+/', '', $input) ?? '';

        if (str_starts_with($mobile, '05')) {
            $mobile = '9665' . substr($mobile, 2);
        }

        if (str_starts_with($mobile, '5') && strlen($mobile) === 9) {
            $mobile = '966' . $mobile;
        }

        return substr($mobile, 0, 12);
    }

    private function homeUrlWithAuth(string $mode): string
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
        $base = $locale === 'en' ? url('/en') : url('/');

        if (class_exists(\Mcamara\LaravelLocalization\Facades\LaravelLocalization::class)) {
            try {
                $localized = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL(
                    $locale,
                    url('/'),
                    [],
                    true
                );

                if (is_string($localized) && $localized !== '') {
                    $base = $localized;
                }
            } catch (\Throwable $e) {
                //
            }
        }

        return $base . '?auth=' . $mode;
    }
}