<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="h-full"
      data-bs-theme="light">
<head>
    @include('partials.head')
    <title>{{ config('app.name', 'Laravel') }}</title>

    <style>
        html, body { font-family: 'Tajawal', sans-serif; }
        [wire\:cloak] { display: none !important; }
    </style>

    @livewireStyles
    @filamentStyles
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 flex items-center justify-center p-6">
    <div class="w-full max-w-md" wire:cloak>
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white border border-slate-200 flex items-center justify-center shadow-sm">
                        <span class="text-[#1FA7A2] font-black">A7</span>
                    </div>
                    <div>
                        <div class="text-slate-900 font-extrabold">{{ __('Amr Seven') }}</div>
                        <div class="text-slate-600 text-sm">{{ __('Business Solutions') }}</div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                {{ $slot }}
            </div>
        </div>
    </div>

    @fluxScripts
    @livewireScripts
    @filamentScripts
</body>
</html>
