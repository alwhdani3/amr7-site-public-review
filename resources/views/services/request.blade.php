@extends('layouts.app')

@section('content')
@php
    $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
    $isAr = $locale === 'ar';

    $serviceTitle = $service
        ? ($isAr
            ? ($service->title_ar ?? $service->title_en ?? $service->slug ?? '')
            : ($service->title_en ?? $service->title_ar ?? $service->slug ?? ''))
        : '';

    $serviceExcerpt = $service
        ? ($isAr
            ? ($service->excerpt_ar ?? $service->excerpt_en ?? '')
            : ($service->excerpt_en ?? $service->excerpt_ar ?? ''))
        : '';
@endphp

<div class="min-h-screen bg-slate-50/60 font-['Tajawal'] pt-32 pb-24 relative overflow-hidden" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-0 end-0 w-[600px] h-[600px] bg-[#1FA7A2]/5 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-0 start-0 w-[500px] h-[500px] bg-emerald-400/5 blur-[120px] rounded-full"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:32px_32px] opacity-40"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start max-w-6xl mx-auto">

            {{-- Service Summary --}}
            <div class="lg:col-span-4 order-1 lg:order-2">
                <div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/40 border border-slate-100 lg:sticky lg:top-32 animate__animated animate__fadeIn">
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 bg-gradient-to-br from-teal-50 to-[#1FA7A2]/10 rounded-[1.5rem] flex items-center justify-center text-[#1FA7A2] mx-auto mb-4 shadow-inner transform rotate-3">
                            <i class="fas fa-file-signature text-3xl" aria-hidden="true"></i>
                        </div>
                        <h1 class="text-xl md:text-2xl font-black text-slate-800 leading-snug">
                            {{ __('Request Service') }}
                        </h1>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-2">
                            {{ __('Fast & Simple Submission') }}
                        </p>
                    </div>

                    @if($service)
                        <div class="space-y-5 border-y border-slate-100 py-6 mb-6">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-medium shrink-0">{{ __('Service') }}:</span>
                                <span class="font-black text-slate-800 text-end">{{ $serviceTitle }}</span>
                            </div>

                            @if(!empty($service->price))
                                <div class="flex justify-between items-center text-sm bg-slate-50 p-3 rounded-xl border border-slate-100/50">
                                    <span class="text-slate-500 font-medium shrink-0">{{ __('Cost') }}:</span>
                                    <span class="font-black text-[#1FA7A2] text-base">{{ $service->price }} {{ __('SAR') }}</span>
                                </div>
                            @endif

                            @if(!empty($service->duration))
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-500 font-medium shrink-0">{{ __('Duration') }}:</span>
                                    <span class="font-bold text-slate-800">{{ $service->duration }}</span>
                                </div>
                            @endif
                        </div>

                        @if(!empty($serviceExcerpt))
                            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 mb-6 relative overflow-hidden">
                                <div class="absolute top-0 start-0 w-1 h-full bg-[#1FA7A2]"></div>
                                <p class="text-sm text-slate-600 leading-relaxed font-medium m-0">
                                    {{ $serviceExcerpt }}
                                </p>
                            </div>
                        @endif
                    @endif

                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-xs text-slate-600 bg-white rounded-xl p-3 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <i class="fas fa-bolt text-amber-500 text-base" aria-hidden="true"></i>
                            <span class="font-bold">{{ __('Quick response from our team') }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-600 bg-white rounded-xl p-3 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <i class="fas fa-shield-check text-emerald-500 text-base" aria-hidden="true"></i>
                            <span class="font-bold">{{ __('Secure handling of your information') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Livewire Form --}}
            <div class="lg:col-span-8 order-2 lg:order-1 animate__animated animate__fadeInUp">
                <livewire:public.service-request-form :service-id="$service->id" />
            </div>
        </div>
    </div>
</div>
@endsection
