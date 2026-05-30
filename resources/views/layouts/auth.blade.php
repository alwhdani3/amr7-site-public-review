<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Amr Seven Business Solutions') }}</title>

    <link rel="icon" href="{{ asset('brand/amr7/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('brand/amr7/amr7-app-icon-180.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @stack('styles')
</head>
<body class="font-['Tajawal'] antialiased text-slate-900 bg-slate-50 min-h-screen relative overflow-x-hidden selection:bg-[#1FA7A2] selection:text-white">

    <div class="fixed inset-0 pointer-events-none -z-10">
        <div class="absolute -top-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-[#1FA7A2]/5 blur-3xl animate-pulse" style="animation-duration: 10s;"></div>
        <div class="absolute top-[30%] -left-[10%] w-[40%] h-[40%] rounded-full bg-teal-500/5 blur-3xl animate-pulse" style="animation-duration: 8s;"></div>
    </div>

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 p-6">
        <div class="mb-8 animate__animated animate__fadeInDown">
            <a href="/">
                <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" class="h-24 w-auto hover:scale-105 transition-transform duration-300 drop-shadow-sm" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-white shadow-2xl shadow-slate-200/50 overflow-hidden rounded-[2.5rem] border border-slate-100 animate__animated animate__fadeInUp">
            {{ $slot }}
        </div>

        <div class="mt-8 text-center text-sm text-slate-400 font-medium animate__animated animate__fadeIn" style="animation-delay: 0.5s">
            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
