<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\LegacySeoCleanup::class);

        // =========================
        // Web Middleware Stack
        // =========================
        $middleware->web(append: [
            \App\Http\Middleware\CleanQueryParameters::class,
            \App\Http\Middleware\SetLocale::class,

            // SEO defaults + robots rules
            \App\Http\Middleware\SetSeoDefaults::class,
            \App\Http\Middleware\NoIndexSensitivePages::class,
        ]);

        // =========================
        // CSRF Exceptions
        // =========================
        // Phase D — payment provider webhooks are server-to-server callbacks
        // and never carry a session/CSRF token. Without this exemption the
        // route Payments\WebhookController::handle would be blocked at 419
        // (Page Expired) before it can return its own honest 410 "disabled"
        // response while config('payments.enabled') stays false.
        //
        // Safety: the controller itself still gates every request behind
        // config('payments.enabled') AND the per-provider webhook flag, so
        // this exemption widens nothing — it only lets the controller run.
        $middleware->validateCsrfTokens(except: [
            'payments/webhook/*',
        ]);

        // =========================
        // Route Middleware Aliases
        // =========================
        $middleware->alias([
            // Project middleware
            'company.selected' => \App\Http\Middleware\EnsureActiveCompany::class,
            'forceLocale'      => \App\Http\Middleware\ForceLocale::class,
            'internal.amr7.api' => \App\Http\Middleware\InternalAmr7ApiTokenMiddleware::class,
            // Mobile API
            'mobile.company'   => \App\Http\Middleware\EnsureMobileActiveCompany::class,

            // LaravelLocalization (mcamara)
            'localeSessionRedirect' => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            'localizationRedirect'  => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            'localeViewPath'        => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Mobile API — envelope موحَّد لكل أخطاء /api/mobile/*
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! $request->is('api/mobile/*')) {
                return null;
            }

            return \App\Support\Api\ApiResponse::renderException($e);
        });
    })
    ->create();
