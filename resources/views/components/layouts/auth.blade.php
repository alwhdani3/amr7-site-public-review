@php
    $brandName = app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions';
    $logo = asset('brand/amr7/amr7-logo-lockup-light.svg');
    $cover = asset('brand/amr7/amr7-og-image-1200x630.png');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $brandName }}</title>

    <link rel="icon" href="{{ asset('brand/amr7/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('brand/amr7/amr7-app-icon-180.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body { font-family: "Tajawal", sans-serif; }
        [wire\:cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>

<body class="min-h-screen antialiased bg-slate-50 text-slate-900 dark:bg-neutral-950 dark:text-neutral-100 relative selection:bg-teal-500 selection:text-white">

    <div class="fixed inset-0 -z-10 h-full w-full bg-white dark:bg-neutral-950 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px] dark:bg-[radial-gradient(#171717_1px,transparent_1px)] opacity-70"></div>

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-5xl animate-fade-in-up">
            <div class="overflow-hidden rounded-3xl border border-slate-200/70 bg-white/80 backdrop-blur-xl shadow-2xl dark:bg-neutral-900/80 dark:border-neutral-800">
                <div class="grid lg:grid-cols-2 min-h-[600px]">

                    <div class="p-8 sm:p-12 flex flex-col justify-center relative order-2 lg:order-1" wire:cloak>

                        <div class="mb-10 flex items-center justify-between gap-4">
                            <a href="{{ url('/') }}" class="flex items-center gap-3 transition hover:opacity-80 group">
                                <img src="{{ $logo }}" alt="{{ $brandName }}" class="h-12 w-auto object-contain group-hover:scale-105 transition-transform duration-300">

                                <div class="text-start hidden sm:block">
                                    <div class="text-sm font-extrabold text-slate-900 dark:text-white leading-tight">
                                        {{ $brandName }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 dark:text-neutral-400 font-mono tracking-wide" dir="ltr">
                                        amr-7.sa
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="mx-auto w-full max-w-sm">
                            {{ $slot }}
                        </div>

                        <div class="mt-10 text-center lg:text-start">
                            <p class="text-xs text-slate-400 dark:text-neutral-600">
                                &copy; {{ now()->year }} {{ $brandName }}. {{ __('All rights reserved.') }}
                            </p>
                        </div>
                    </div>

                    <div class="relative hidden lg:flex flex-col justify-end border-s border-slate-100 dark:border-neutral-800 overflow-hidden order-1 lg:order-2 group">

                        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-[2000ms] group-hover:scale-110"
                             style="background-image: url('{{ $cover }}');">
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-[#0f172a]/60 to-transparent"></div>

                        <div class="absolute top-0 right-0 p-8 opacity-20">
                            <svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="50" cy="50" r="40" stroke="white" stroke-width="2" stroke-dasharray="10 5"/>
                            </svg>
                        </div>

                        <div class="relative p-12 text-white z-10">
                            <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-teal-400 shadow-lg">
                                <i class="fas fa-quote-right text-2xl"></i>
                            </div>

                            <h3 class="text-3xl font-bold leading-tight mb-3 tracking-tight">
                                {{ __('Business Solutions Simplified') }}
                            </h3>

                            <p class="text-sm text-slate-300 leading-relaxed mb-8 max-w-sm opacity-90">
                                {{ __('We are here to achieve your vision and help you accomplish your business efficiently and professionally.') }}
                            </p>

                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('contact.index') }}"
                                   class="inline-flex items-center justify-center rounded-full bg-white text-slate-900 px-6 py-2.5 text-sm font-bold shadow-lg shadow-white/10 transition transform hover:-translate-y-0.5 hover:bg-slate-100">
                                    {{ __('Contact Us') }}
                                </a>

                                <button type="button" onclick="history.back()"
                                        class="inline-flex items-center justify-center rounded-full bg-white/10 px-6 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20 border border-white/10">
                                    {{ __('Go Back') }}
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
