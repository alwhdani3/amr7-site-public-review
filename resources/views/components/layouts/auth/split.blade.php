<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="h-full">
<head>
    @include('partials.head')
    <title>{{ config('app.name', 'Amr Seven Business Solutions') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

    <style>
        html, body { font-family: 'Tajawal', sans-serif; }
        [wire\:cloak] { display: none !important; }
    </style>

    @livewireStyles
    @filamentStyles
</head>

<body class="min-h-screen bg-white antialiased text-slate-900">
<div class="min-h-screen flex">
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-slate-50" wire:cloak>
        <div class="absolute inset-0 opacity-40 bg-[radial-gradient(#1FA7A2_1px,transparent_1px)] [background-size:24px_24px]"></div>
        <div class="relative z-10 p-12 flex flex-col justify-between w-full">
            <div>
                <div class="inline-flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white border border-slate-200 flex items-center justify-center shadow-sm">
                        <span class="text-[#1FA7A2] font-black">A7</span>
                    </div>
                    <div>
                        <div class="text-slate-900 font-extrabold text-xl">{{ __('Amr Seven') }}</div>
                        <div class="text-slate-600 text-sm">{{ __('Business Solutions') }}</div>
                    </div>
                </div>
            </div>

            <div class="max-w-xl">
                <h1 class="text-4xl font-black leading-tight text-slate-900">{{ __('Welcome back') }}</h1>
                <p class="mt-4 text-slate-600 leading-relaxed">{{ __('Manage requests, track progress, and access your account securely.') }}</p>
                <div class="mt-8 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-slate-200 shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-slate-700 text-sm font-semibold">{{ __('Secure access') }}</span>
                </div>
            </div>

            <div class="text-slate-500 text-sm">{{ __('© :year Amr Seven Business Solutions. All rights reserved.', ['year' => date('Y')]) }}</div>
        </div>
    </div>

    <div class="flex-1 flex items-center justify-center p-6 bg-white" wire:cloak>
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>
</div>

@fluxScripts
@livewireScripts
@filamentScripts
</body>
</html>
