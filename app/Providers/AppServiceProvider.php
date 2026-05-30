<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\ServicePlatform;
use App\Observers\PostObserver;
use App\Support\NotificationGuard;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Post::observe(PostObserver::class);

        $this->bootNotificationSafeguards();

        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            return (new MailMessage)
                ->subject(__('Verify Email Address') . ' | ' . config('app.name'))
                ->view('emails.auth.verify-email-card', [
                    'url'  => $url,
                    'user' => $notifiable,
                ]);
        });

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject(__('Reset Password') . ' | ' . config('app.name'))
                ->view('emails.auth.reset-password-card', [
                    'url'  => $url,
                    'user' => $notifiable,
                ]);
        });

        View::composer('partials.company-switcher', function ($view) {
            if (! auth()->check()) {
                $view->with([
                    'companies'     => collect(),
                    'activeId'      => null,
                    'activeCompany' => null,
                ]);

                return;
            }

            $companies = auth()->user()->companies()->orderBy('name')->get();
            $activeId = session('active_company_id');
            $activeCompany = $companies->firstWhere('id', $activeId) ?? $companies->first();

            $view->with(compact('companies', 'activeId', 'activeCompany'));
        });

        $this->bootHeaderComposer();
    }

    /**
     * Safe-by-default mail interception. When NOTIFICATIONS_DRY_RUN=true
     * and a test email is configured, Mail::alwaysTo() reroutes every
     * outgoing message to the test address. When dry-run is on with no
     * test address, we leave mail untouched but log a warning so the
     * operator knows real sends are skipped at the channel layer.
     */
    protected function bootNotificationSafeguards(): void
    {
        if (! NotificationGuard::isDryRun()) {
            return;
        }

        $testRecipient = NotificationGuard::emailTestRecipient();

        if ($testRecipient) {
            Mail::alwaysTo($testRecipient);

            return;
        }

        Mail::alwaysTo('dryrun-blackhole@amr-7.sa');

        Log::info('notifications.dry_run.boot', [
            'mode'    => 'mail-blackholed',
            'reason'  => 'NOTIFICATIONS_DRY_RUN=true and no NOTIFICATIONS_ALLOWED_TEST_EMAIL set — all outbound mail routed to blackhole address.',
        ]);
    }

    protected function bootHeaderComposer(): void
    {
        View::composer('partials.header', function ($view) {
            $currentLocale = app()->getLocale() === 'en' ? 'en' : 'ar';
            $targetLocale  = $currentLocale === 'ar' ? 'en' : 'ar';
            $langLabel     = $currentLocale === 'ar' ? 'English' : 'العربية';

            $safeUrl = function (string $name, array $params = []) {
                return Route::has($name) ? route($name, $params) : null;
            };

            $cacheKey = "header_menu_platforms_v5_{$currentLocale}";
            $menuPlatforms = Cache::remember($cacheKey, now()->addHours(6), function () {
                return ServicePlatform::query()
                    ->where('is_active', true)
                    ->orderBy('id', 'asc')
                    ->limit(20)
                    ->get(['id', 'name_ar', 'name_en']);
            });

            $aboutItems = [
                ['name' => __('About'),            'url' => $safeUrl('about'),          'icon' => 'fa-info-circle'],
                ['name' => __('Privacy Policy'),   'url' => $safeUrl('privacy.policy'), 'icon' => 'fa-user-shield'],
                ['name' => __('Terms Conditions'), 'url' => $safeUrl('using.policy'),   'icon' => 'fa-file-contract'],
                ['name' => __('Partner With Us'),  'url' => $safeUrl('contact.index') ? ($safeUrl('contact.index') . '?as=partner') : null, 'icon' => 'fa-handshake'],
                ['name' => __('Bank Accounts'),    'url' => $safeUrl('banks.index'),    'icon' => 'fa-university'],
                ['name' => __('Company Profile'),  'url' => is_file(public_path('company-profile.pdf')) ? asset('company-profile.pdf') : null, 'icon' => 'fa-file-pdf'],
            ];

            $socialLinks = array_filter([
                'haraj'     => (string) config('amr7.social_links.haraj', ''),
                'linkedin'  => (string) config('amr7.social_links.linkedin', ''),
                'x'         => (string) config('amr7.social_links.x', ''),
                'instagram' => (string) config('amr7.social_links.instagram', ''),
                'tiktok'    => (string) config('amr7.social_links.tiktok', ''),
            ]);

            $view->with(compact(
                'menuPlatforms',
                'currentLocale',
                'targetLocale',
                'langLabel',
                'aboutItems',
                'socialLinks'
            ));
        });
    }
}