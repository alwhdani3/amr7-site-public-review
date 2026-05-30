<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="h-full">
<head>
    @include('partials.head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

    <style>
        html, body { font-family: "Tajawal", sans-serif; }
        [wire\:cloak] { display: none !important; }
    </style>

    @livewireStyles
    @filamentStyles
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 antialiased relative">
    <div class="absolute inset-0 -z-10 h-full w-full bg-white bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px]"></div>

    <div class="flex min-h-screen flex-col items-center justify-center p-6 md:p-10" wire:cloak>
        <div class="w-full max-w-[420px] flex flex-col gap-8">
            <a href="{{ route('dashboard') }}"
               class="flex flex-col items-center gap-2 self-center transition-transform hover:scale-105"
               wire:navigate>
                <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}"
                     class="h-14 w-auto object-contain drop-shadow-sm">
            </a>

            <div class="flex flex-col gap-6">
                <div class="rounded-3xl border border-slate-200 bg-white/80 backdrop-blur-xl shadow-2xl shadow-slate-200/50">
                    <div class="p-8 sm:p-10">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <div class="text-center text-xs text-slate-400 font-medium">
                &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
            </div>
        </div>
    </div>

    @fluxScripts
    @livewireScripts
    @filamentScripts
</body>
</html>
