<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// Controllers
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Payments\WebhookController as PaymentWebhookController;
use App\Http\Controllers\CompanyFileDownloadController;
use App\Http\Controllers\CompanyInvitationController;
use App\Http\Controllers\CompanySelectionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FsFileDownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SecureFileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Public\PackageController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\Landing\CompanyFormationRiyadhController;
use App\Http\Controllers\Public\Landing\CompanyLiquidationController;
use App\Http\Controllers\Public\Landing\ForeignInvestmentController;

// Models
use App\Models\Package;
use App\Models\Post;
use App\Models\Service;

// Livewire Components
use App\Livewire\BankAccountsIndex;
use App\Livewire\BlogIndex;
use App\Livewire\Dashboard;
use App\Livewire\EmployeesPanel;
use App\Livewire\FinancialStatements\Create as FsCreate;
use App\Livewire\FinancialStatements\Index as FsIndex;
use App\Livewire\FinancialStatements\Portal as FSPortal;
use App\Livewire\FinancialStatements\Show as FsShow;
use App\Livewire\Public\ServicesPage;
use App\Livewire\Settings\Appearance as SettingsAppearance;
use App\Livewire\Settings\Password as SettingsPassword;
use App\Livewire\Settings\Profile as SettingsProfile;
use App\Livewire\Settings\TwoFactor as SettingsTwoFactor;
use App\Livewire\Dashboard\TicketsPanel;

/*
|--------------------------------------------------------------------------
| 1. Switch Language Route
|--------------------------------------------------------------------------
*/
Route::get('/lang/{locale}', function (Request $request, string $locale) {
    abort_unless(in_array($locale, ['ar', 'en'], true), 404);

    session(['locale' => $locale]);

    if (auth()->check()) {
        auth()->user()->update(['locale' => $locale]);
    }

    $redirect = $request->query('redirect');
    $defaultTarget = $locale === 'en' ? url('/en') : url('/');

    if (is_string($redirect) && $redirect !== '' && str_starts_with($redirect, '/')) {
        $redirect = url($redirect);
    }

    if (! is_string($redirect) || $redirect === '') {
        $redirect = $request->headers->get('referer') ?: $defaultTarget;
    }

    $redirectHost = parse_url($redirect, PHP_URL_HOST);
    if ($redirectHost && $redirectHost !== $request->getHost()) {
        $redirect = $defaultTarget;
    }

    $redirectPath = '/' . ltrim((string) parse_url($redirect, PHP_URL_PATH), '/');
    $redirectPath = preg_replace('#/+#', '/', $redirectPath) ?: '/';

    if ($redirectPath === '/en/en') {
        $redirectPath = '/en';
        $redirect = url('/en');
    } elseif (str_starts_with($redirectPath, '/en/en/')) {
        $redirectPath = '/en/' . ltrim(substr($redirectPath, 7), '/');
        $redirect = url($redirectPath);
    } elseif ($redirectPath === '/ar') {
        $redirectPath = '/';
        $redirect = url('/');
    } elseif (str_starts_with($redirectPath, '/ar/')) {
        $redirectPath = '/' . ltrim(substr($redirectPath, 4), '/');
        $redirect = url($redirectPath);
    }

    $blockedPaths = [
        '/forgot-password', '/en/forgot-password',
        '/company/select', '/en/company/select',
    ];

    $duplicateQueryKeys = config('seo.duplicate_query_keys', []);
    $redirectQuery = [];
    $redirectQueryString = parse_url($redirect, PHP_URL_QUERY);

    if (is_string($redirectQueryString) && $redirectQueryString !== '') {
        parse_str($redirectQueryString, $redirectQuery);

        if (array_intersect($duplicateQueryKeys, array_keys($redirectQuery))) {
            $redirect = url($redirectPath);
        }
    }

    if (in_array($redirectPath, $blockedPaths, true) || str_starts_with($redirectPath, '/lang/')) {
        $redirect = $defaultTarget;
    } elseif (preg_match('#^/(?:en/)?services/[0-9]+$#', $redirectPath)) {
        $redirect = url('/services');
    } elseif (preg_match('#^/(?:en/)?packages/[0-9]+$#', $redirectPath)) {
        $redirect = url('/packages');
    } elseif (preg_match('#^/(?:en/)?services/platform/[^/]+$#', $redirectPath) && parse_url($redirect, PHP_URL_QUERY)) {
        $redirect = url(preg_replace('#^/en/#', '/', $redirectPath));
    }

    $target = LaravelLocalization::getLocalizedURL($locale, $redirect, [], true);

    return redirect()
        ->to($target, 301)
        ->withHeaders(['X-Robots-Tag' => 'noindex, follow']);
})->name('switch.language');

/*
|--------------------------------------------------------------------------
| 2. Main Localized Routes Group
|--------------------------------------------------------------------------
*/
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['web', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
], function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');

    // تحويلات SEO للروابط القديمة تتم في App\Http\Middleware\LegacySeoCleanup::staticRedirectTarget
    // (about-us, contact, terms, company-formation-saudi-arabia, vision, و الرابط العربي القديم لـ "من نحن").

    Route::get('/company-formation/riyadh', [CompanyFormationRiyadhController::class, 'show'])->name('public.landing.company-formation-riyadh');
    Route::get('/foreign-investment', [ForeignInvestmentController::class, 'show'])->name('landing.foreign_investment');
    Route::get('/company-liquidation', [CompanyLiquidationController::class, 'show'])->name('landing.liquidation');

    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');

    Route::get('/faq', function () {
        $packages = Package::query()->active()->latest('id')->get();
        return view('faq', compact('packages'));
    })->name('faq');

    Route::get('/banks', BankAccountsIndex::class)->name('banks.index');
    Route::view('/using-policy', 'using-policy')->name('using.policy');
    Route::view('/about', 'about')->name('about');
    Route::view('/vision', 'vision')->name('vision');
    Route::view('/privacy-policy', 'privacy-policy')->name('privacy.policy');

    // الخدمات والمدونة
    Route::get('/services/request/{service_id}', function (Request $request, int $service_id) {
        if ($request->user()) {
            return redirect()->route('dashboard', [
                'section' => 'requests',
                'service' => $service_id,
            ]);
        }

        return app(ServiceController::class)->requestService($service_id);
    })->name('services.request');
    Route::get('/services', ServicesPage::class)->name('services.index');
    Route::get('/services/platform/{platform:slug}', ServicesPage::class)->name('services.platform');

    Route::get('/services/{id}', function (int $id) {
        $service = Service::find($id);

        if (! $service || empty($service->slug)) {
            abort(410);
        }

        return redirect()->to(
            LaravelLocalization::getLocalizedURL(app()->getLocale(), url('/services/' . $service->slug)),
            301
        );
    })->whereNumber('id')->name('services.legacy-id');

    Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');

    Route::get('/blog', BlogIndex::class)->name('blog.index');

    Route::get('/blog/{id}', function (int $id) {
        $post = Post::find($id);

        if (! $post || empty($post->slug)) {
            abort(410);
        }

        return redirect()->to(
            LaravelLocalization::getLocalizedURL(app()->getLocale(), url('/blog/' . $post->slug)),
            301
        );
    })->whereNumber('id')->name('blog.legacy-id');

    Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

    Route::get('/contact-us', [ContactController::class, 'index'])->name('contact.index');

    // القوائم المالية
    Route::prefix('financial-statements')->name('financial-statements.')->group(function () {
        Route::get('/portal', FSPortal::class)->name('portal');
        Route::get('/create', FsCreate::class)->name('create');
        Route::get('/view/{request:public_id}', FsShow::class)->name('show');

        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('/dashboard', FsIndex::class)->name('index');
            Route::get('files/{file}/download', [FsFileDownloadController::class, 'download'])->name('file.download');
        });
    });

    Route::get('/fs/{request:public_id}', FsShow::class)->name('fs.show');

    // Phase A — team invitations acceptance. Lives outside the
    // `auth` group so guests land on the same URL, see a status page,
    // and get bounced to register/login with the token kept in
    // session. The controller hashes the URL token before any lookup.
    Route::get('/invitations/{token}/accept', [CompanyInvitationController::class, 'accept'])
        ->where('token', '[A-Za-z0-9]{32,64}')
        ->middleware('throttle:invitations.accept')
        ->name('company.invitations.accept');

    // Phase C — phone-login OTP foundation. Disabled by default
    // (config('otp.enabled') === false). When disabled, the controller
    // short-circuits with a clear notice; no SMS is ever attempted.
    // Fortify email + password login is untouched.
    Route::get('/login/phone', [OtpController::class, 'showPhoneLogin'])
        ->name('login.phone');
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/login/phone/send', [OtpController::class, 'send'])
            ->name('login.phone.send');
        Route::post('/login/phone/verify', [OtpController::class, 'verify'])
            ->name('login.phone.verify');
    });

    // Phase D — payment gateway webhook scaffold. Returns HTTP 410 until
    // BOTH config('payments.enabled') AND the per-provider webhook flag
    // are flipped true on the operator's host. No customer charges ever
    // happen via this branch — manual driver only records bank-transfer
    // payments via the existing backoffice flow.
    Route::post('/payments/webhook/{provider}', [PaymentWebhookController::class, 'handle'])
        ->where('provider', '[a-z][a-z0-9_-]{1,32}')
        ->middleware('throttle:30,1')
        ->name('payments.webhook');

    require __DIR__ . '/auth.php';

    /* --- منطقة الأعضاء المؤمنة --- */
    Route::middleware(['auth'])->group(function () {
        Route::redirect('/settings', '/settings/profile');
        Route::get('/settings/profile', SettingsProfile::class)->name('profile.edit');
        Route::get('/settings/password', SettingsPassword::class)->name('user-password.edit');
        Route::get('/settings/two-factor', SettingsTwoFactor::class)->name('two-factor.show');
        Route::get('/settings/appearance', SettingsAppearance::class)->name('appearance.edit');

        Route::post('/logout', function (Request $request) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->to(
                LaravelLocalization::getLocalizedURL(app()->getLocale(), url('/')),
                302
            );
        })->name('logout');

        Route::middleware(['verified'])->group(function () {
            Route::get('/staff', EmployeesPanel::class)->name('staff.index');
            Route::get('/company/select', [CompanySelectionController::class, 'index'])->name('company.select');
            Route::post('/company/select', [CompanySelectionController::class, 'store'])->name('company.select.store');
            Route::post('/company/switch/{company}', [CompanySelectionController::class, 'switch'])->name('company.switch');
            Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('company.update');

            // --- مركز الملفات الآمن ---
            Route::prefix('files')->group(function () {
                Route::get('company-docs/{companyDocument}/view', [SecureFileController::class, 'companyDocView'])->name('company.docs.view');
                Route::get('company-docs/{companyDocument}/download', [SecureFileController::class, 'companyDocDownload'])->name('company.docs.download');

                Route::get('attachments/{attachment}/view', [SecureFileController::class, 'attachmentView'])->name('attachments.view');
                Route::get('attachments/{attachment}/download', [SecureFileController::class, 'attachmentDownload'])->name('attachments.download');

                Route::get('company-files/{companyFile}/view', [SecureFileController::class, 'companyFileView'])->name('company.files.view');
                Route::get('company-files/{companyFile}/download', [SecureFileController::class, 'companyFileDownload'])->name('company.files.download');
            });

            Route::middleware('company.selected')->group(function () {
                Route::get('/dashboard', Dashboard::class)->name('dashboard');
                Route::get('/dashboard/tickets', TicketsPanel::class)->name('tickets.index');
            });
        });
    });
});

/*
|--------------------------------------------------------------------------
| 3. SEO & Legacy Cleanup
|--------------------------------------------------------------------------
|
| كل منطق تنظيف الـ legacy URLs انتقل إلى App\Http\Middleware\LegacySeoCleanup
| (مُلصَق كـ prepend في bootstrap/app.php). يغطّي:
|
|   - 301: /en/en/*, /ar/ar/*, /ar/* (للمسارات المعروفة)، و alias-list:
|          /contact, /terms, /about-us, /company-formation-saudi-arabia,
|          /من-نحن-شركة-امر-سبعة-لحلول-الأعمال
|   - 301: /services/{id} العددي إذا للخدمة slug
|   - 410: /wp-content, /wp-admin, /wp-includes, /wp-*.php, /xmlrpc.php,
|          /feed, /comments/feed, /tag, /category, /author, /page,
|          /portfolio, /project-cat, /product*, /shop, /cart, /checkout,
|          /my-account, /wishlist, /elementor-*
|   - 410: / مع ?p= أو ?page_id= (روابط WordPress legacy)
|
| المسارات المُكررة هنا حُذفت — الـ middleware هو المصدر الموحّد للحقيقة.
*/
