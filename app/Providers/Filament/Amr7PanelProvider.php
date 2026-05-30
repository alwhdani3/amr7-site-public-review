<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Assets\Css;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Amr7PanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('amr7')
            ->path('amr7')

            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()

            ->darkMode(false)
            ->defaultThemeMode(ThemeMode::Light)

            ->brandName('شركة آمر سبعة لحلول الأعمال')
            ->favicon(asset('brand/amr7/favicon.ico'))
            ->brandLogo(asset('brand/amr7/amr7-logo-lockup-light.png'))
            ->brandLogoHeight('3rem')

            ->colors([
                'primary' => Color::hex('#1FA7A2'),
                'gray'    => Color::Slate,
                'info'    => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'danger'  => Color::Rose,
            ])
            ->font('Tajawal')

            // ✅ Filament 5 Theme (استبدل theme.css بملفك amr7.css)
->viteTheme('resources/css/filament/theme.css')

            // ✅ تحميل الخط للوحة فقط (اختياري لكن مفيد إذا اللوحة ما تقرأ <head> الخاص بالموقع)
            ->assets([
                Css::make(
                    'amr7-fonts',
                    'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Tajawal:wght@300;400;500;700;800&display=swap'
                ),
            ])

            ->userMenuItems([
                MenuItem::make('lang-en')
                    ->label('English')
                    ->url(fn () => route('switch.language', ['locale' => 'en']))
                    ->visible(fn () => app()->getLocale() === 'ar')
                    ->icon('heroicon-o-language'),

                MenuItem::make('lang-ar')
                    ->label('العربية')
                    ->url(fn () => route('switch.language', ['locale' => 'ar']))
                    ->visible(fn () => app()->getLocale() === 'en')
                    ->icon('heroicon-o-language'),
            ])

            ->navigationGroups([
                'الإدارة',
                'العملاء والخدمات',
                'الوثائق والتذاكر',
                'المحتوى',
                'المالية',
                'الاشتراكات والالتزامات',
                'التشغيل الآلي والذكاء',
                'النظام',
                'التسويق',
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,

                StartSession::class,
                \App\Http\Middleware\SetLocale::class,

                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
