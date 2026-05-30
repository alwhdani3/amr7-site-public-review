@extends('layouts.app')

@php
    $seoTitle = __('contact_seo_title');
    $seoDescription = __('contact_seo_description');

    $brandName = __('brand_name');
    $siteUrl   = url('/');
    $phoneIntl = '+966505336956';
    $phoneShow = '050 533 6956';
    $email     = 'info@amr-7.sa';
    $address   = __('city_riyadh') . ' - ' . __('area_nafl') . ' - ' . __('street_abubakar');

    $schema = [
        "@context" => "https://schema.org",
        "@type" => "ContactPage",
        "name" => $seoTitle,
        "description" => $seoDescription,
        "url" => url()->current(),
        "mainEntity" => [
            "@type" => "Organization",
            "name" => $brandName,
            "url" => $siteUrl,
            "logo" => asset('brand/amr7/amr7-logo-lockup-light.png'),
            "contactPoint" => [
                "@type" => "ContactPoint",
                "telephone" => $phoneIntl,
                "contactType" => "customer service",
                "email" => $email,
                "areaServed" => "SA",
                "availableLanguage" => ["ar", "en"]
            ],
            "address" => [
                "@type" => "PostalAddress",
                "streetAddress" => $address,
                "addressLocality" => "Riyadh",
                "addressCountry" => "SA"
            ]
        ]
    ];
@endphp

@push('head')
    <script type="application/ld+json">
        {!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

@section('content')
    <div class="min-h-screen bg-slate-50 font-['Tajawal'] relative overflow-x-hidden pt-24 pb-20" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

        <div class="fixed inset-0 pointer-events-none z-0 opacity-40">
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#1FA7A2]/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-teal-500/5 rounded-full blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:24px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10">

            <header class="text-center mb-16 max-w-3xl mx-auto animate__animated animate__fadeInDown">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-teal-50 border border-teal-100 text-[#1FA7A2] text-sm font-bold mb-6 shadow-sm">
                    <i class="fas fa-headset"></i> {{ __('customer_service_center') }}
                </div>

                <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-6 leading-tight">
                    {{ __('we_are_here') }}
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#1FA7A2] to-[#167F7B]">{{ __('to_serve_you') }}</span>
                </h1>

                <p class="text-lg text-slate-500 leading-relaxed">
                    {{ __('contact_hero_desc') }}
                </p>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <div class="lg:col-span-7 animate__animated animate__fadeInRight animate__delay-1s">
                    <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/60 border border-slate-100 p-8 md:p-10 relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#1FA7A2] to-teal-400"></div>

                        <div class="mb-8 border-b border-slate-100 pb-6">
                            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-xl bg-[#f0fdfa] flex items-center justify-center text-[#1FA7A2]">
                                    <i class="fas fa-file-signature text-lg"></i>
                                </div>
                                {{ __('new_service_request') }}
                            </h2>
                            <p class="text-slate-400 text-sm flex items-center gap-2">
                                <i class="far fa-clock"></i> {{ __('response_time_note') }}
                            </p>
                        </div>

                        <livewire:public.service-request-form />
                    </div>
                </div>

                <aside class="lg:col-span-5 animate__animated animate__fadeInLeft animate__delay-1s">
                    <div class="sticky top-28 space-y-4">

                        <a href="tel:{{ $phoneIntl }}" class="group flex items-center gap-5 bg-white border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-lg hover:border-[#1FA7A2]/30 transition-all duration-300">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <i class="fas fa-phone-alt animate__animated animate__tada animate__infinite animate__slow"></i>
                            </div>
                            <div class="flex-grow">
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('unified_number') }}</span>
                                <h3 class="text-lg font-black text-slate-800 font-mono ltr:text-left rtl:text-right" dir="ltr">{{ $phoneShow }}</h3>
                            </div>
                            <div class="text-slate-300 group-hover:text-[#1FA7A2] group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-all">
                                <i class="fas fa-chevron-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}"></i>
                            </div>
                        </a>

                        <a href="https://wa.me/966505336956" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-5 bg-white border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-lg hover:border-[#1FA7A2]/30 transition-all duration-300">
                            <div class="w-14 h-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center text-2xl shrink-0 group-hover:bg-green-600 group-hover:text-white transition-colors">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="flex-grow">
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('live_chat') }}</span>
                                <h3 class="text-lg font-bold text-slate-800">{{ __('contact_via_whatsapp') }}</h3>
                            </div>
                            <div class="text-slate-300 group-hover:text-[#1FA7A2] group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-all">
                                <i class="fas fa-chevron-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}"></i>
                            </div>
                        </a>

                        <a href="mailto:{{ $email }}" class="group flex items-center gap-5 bg-white border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-lg hover:border-[#1FA7A2]/30 transition-all duration-300">
                            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="flex-grow">
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('official_email') }}</span>
                                <h3 class="text-lg font-bold text-slate-800 font-mono">{{ $email }}</h3>
                            </div>
                            <div class="text-slate-300 group-hover:text-[#1FA7A2] group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-all">
                                <i class="fas fa-chevron-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}"></i>
                            </div>
                        </a>

                        <div class="flex items-center gap-5 bg-white border border-slate-200 p-5 rounded-2xl shadow-sm">
                            <div class="w-14 h-14 rounded-2xl bg-teal-50 text-[#1FA7A2] flex items-center justify-center text-xl shrink-0">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('headquarters') }}</span>
                                <h4 class="text-base font-bold text-slate-800">{{ __('city_riyadh') }} - {{ __('area_nafl') }}</h4>
                                <small class="text-[#1FA7A2] font-bold">{{ __('street_abubakar') }}</small>
                            </div>
                        </div>

                    </div>
                </aside>

            </div>
        </div>

        @if(view()->exists('livewire.bank-accounts-index'))
            <section class="mt-20 pt-16 pb-12 bg-white border-t border-slate-100 relative z-10">
                <div class="container mx-auto px-4">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-10">
                        <div>
                            <h2 class="text-2xl font-extrabold text-slate-800 flex items-center gap-3 mb-2">
                                <i class="fas fa-university text-[#1FA7A2]"></i> {{ __('approved_bank_accounts') }}
                            </h2>
                            <p class="text-slate-500 text-sm">{{ __('bank_transfer_note') }}</p>
                        </div>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#1FA7A2]/5 text-[#1FA7A2] text-sm font-bold border border-[#1FA7A2]/10">
                            <i class="fas fa-check-circle"></i> {{ __('verified_accounts') }}
                        </span>
                    </div>

                    <livewire:bank-accounts-index :apply-page-seo="false" />
                </div>
            </section>
        @endif

    </div>
@endsection