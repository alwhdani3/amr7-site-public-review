@extends('layouts.app')

@php
    // إعدادات الـ SEO (كما هي)
    if (class_exists('\Artesaos\SEOTools\Facades\SEOTools')) {
        $canonicalUrl = url(app()->getLocale() === 'en' ? '/en/faq' : '/faq');

        \Artesaos\SEOTools\Facades\SEOTools::setTitle(__('faq.seo_title'));
        \Artesaos\SEOTools\Facades\SEOTools::setDescription(__('faq.seo_description'));
        \Artesaos\SEOTools\Facades\SEOTools::setCanonical($canonicalUrl);
    }

    // مصفوفات الأسئلة (كما هي)
    $generalQuestions = [
        ['q' => __('faq.q.choose_amr7.q'), 'a' => __('faq.q.choose_amr7.a')],
        ['q' => __('faq.q.who_are_we.q'), 'a' => __('faq.q.who_are_we.a')],
        ['q' => __('faq.q.platforms.q'), 'a' => __('faq.q.platforms.a')],
        ['q' => __('faq.q.remote_or_visit.q'), 'a' => __('faq.q.remote_or_visit.a')],
        ['q' => __('faq.q.mixed_companies.q'), 'a' => __('faq.q.mixed_companies.a')],
        ['q' => __('faq.q.payment_mechanism.q'), 'a' => __('faq.q.payment_mechanism.a')],
    ];

    $packageQuestions = [
        ['q' => __('faq.q.packages_difference.q'), 'a' => __('faq.q.packages_difference.a')],
        ['q' => __('faq.q.extra_service.q'), 'a' => __('faq.q.extra_service.a')],
        ['q' => __('faq.q.installments.q'), 'a' => __('faq.q.installments.a')],
        ['q' => __('faq.q.working_hours.q'), 'a' => __('faq.q.working_hours.a')],
        ['q' => __('faq.q.outside_hours.q'), 'a' => __('faq.q.outside_hours.a')],
        ['q' => __('faq.q.auto_work.q'), 'a' => __('faq.q.auto_work.a')],
    ];

    // إعداد الـ Schema (كما هو)
    $faqForSchema = array_merge($generalQuestions, $packageQuestions);
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($faqForSchema)->map(function ($item) {
            return [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['a'],
                ],
            ];
        })->values()->all(),
    ];

    $isAr = app()->getLocale() === 'ar';
    $dir  = $isAr ? 'rtl' : 'ltr';
@endphp

@section('content')
<div class="min-h-screen bg-slate-50 font-['Tajawal']" dir="{{ $dir }}">

    {{-- Hero Section --}}
    <section class="relative pt-24 pb-20 overflow-hidden bg-gradient-to-b from-white to-slate-100">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-40 pointer-events-none" 
             style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 40px 40px;">
        </div>
        
        {{-- Ambient Blob Effect --}}
        <div class="absolute top-[-50%] left-1/2 -translate-x-1/2 w-[600px] h-[600px] rounded-full bg-[#44BDB8]/15 blur-[100px] pointer-events-none"></div>

        <div class="container mx-auto px-4 relative z-10 text-center">
            {{-- Badge --}}
            <div class="mb-6 animate__animated animate__fadeInDown">
                <span class="inline-flex items-center px-6 py-2 rounded-full bg-[#1FA7A2]/10 text-[#1FA7A2] font-bold border border-[#1FA7A2]/20 shadow-sm">
                    <i class="fas fa-headset mx-2"></i> {{ __('faq.support_center_badge') }}
                </span>
            </div>
            
            {{-- Title --}}
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-6 animate__animated animate__fadeInUp">
                {{ __('faq.title_before') }} 
                <span class="bg-clip-text text-transparent bg-gradient-to-br from-[#1FA7A2] to-[#167F7B]">
                    {{ __('faq.title_highlight') }}
                </span>
            </h1>
            
            {{-- Subtitle --}}
            <p class="text-lg text-slate-500 max-w-4xl mx-auto leading-loose animate__animated animate__fadeInUp delay-100">
                {{ __('faq.subtitle') }}
            </p>
        </div>
    </section>

    {{-- Main Content with Alpine.js Tabs --}}
    <section class="py-16 bg-white" x-data="{ activeTab: 'general' }">
        <div class="container mx-auto px-4">

            {{-- Tabs Navigation --}}
            <div class="flex flex-wrap justify-center gap-4 mb-12 animate__animated animate__fadeInUp">
                @foreach(['general' => 'faq.tab.general', 'packages' => 'faq.tab.packages', 'services' => 'faq.tab.service_packages', 'site-orders' => 'faq.tab.site_orders'] as $key => $label)
                    <button @click="activeTab = '{{ $key }}'" 
                            :class="activeTab === '{{ $key }}' ? 'bg-[#1FA7A2] text-white shadow-lg shadow-[#1FA7A2]/20 transform -translate-y-1' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200'"
                            class="px-6 py-3 rounded-full font-bold transition-all duration-300">
                        {{ __($label) }}
                    </button>
                @endforeach
            </div>

            {{-- Tabs Content Container --}}
            <div class="min-h-[400px]">
                
                {{-- 1. General FAQ Tab --}}
                <div x-show="activeTab === 'general'" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="grid grid-cols-1 md:grid-cols-2 gap-6"
                     x-data="{ activeAccordion: null }">
                    
                    @foreach($generalQuestions as $index => $item)
                        <div class="group bg-white border border-slate-200 rounded-3xl overflow-hidden transition-all duration-300 hover:border-[#1FA7A2] hover:shadow-xl hover:shadow-[#1FA7A2]/5">
                            {{-- Accordion Header --}}
                            <button @click="activeAccordion = activeAccordion === {{ $index }} ? null : {{ $index }}" 
                                    class="w-full text-start px-6 py-5 flex items-center gap-4 focus:outline-none"
                                    :class="activeAccordion === {{ $index }} ? 'bg-[#1FA7A2]/5' : 'bg-white'">
                                
                                {{-- Logo Icon --}}
                                <div class="w-10 h-10 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center flex-shrink-0 transition-all duration-300 group-hover:bg-[#1FA7A2] group-hover:scale-110 group-hover:rotate-6">
                                    <img src="{{ asset('brand/amr7/amr7-mark-light.svg') }}" class="h-5 w-auto opacity-90 transition-all duration-300 group-hover:brightness-0 group-hover:invert" alt="" aria-hidden="true">
                                </div>

                                <span class="font-bold text-slate-800 flex-grow group-hover:text-[#1FA7A2] transition-colors">
                                    {{ $item['q'] }}
                                </span>

                                <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300"
                                   :class="activeAccordion === {{ $index }} ? 'rotate-180 text-[#1FA7A2]' : ''"></i>
                            </button>

                            {{-- Accordion Body --}}
                            <div x-show="activeAccordion === {{ $index }}" x-collapse>
                                <div class="px-6 pb-6 pt-2">
                                    <div class="p-4 rounded-xl bg-slate-50 border-r-4 rtl:border-r-4 ltr:border-l-4 border-[#1FA7A2]">
                                        <p class="text-sm text-slate-600 leading-loose m-0">
                                            {{ $item['a'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- 2. Packages FAQ Tab --}}
                <div x-show="activeTab === 'packages'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="grid grid-cols-1 md:grid-cols-2 gap-6"
                     style="display: none;"
                     x-data="{ activeAccordion: null }">

                    @foreach($packageQuestions as $index => $item)
                        <div class="group bg-white border border-slate-200 rounded-3xl overflow-hidden transition-all duration-300 hover:border-[#1FA7A2] hover:shadow-xl hover:shadow-[#1FA7A2]/5">
                            <button @click="activeAccordion = activeAccordion === {{ $index }} ? null : {{ $index }}"
                                    class="w-full text-start px-6 py-5 flex items-center gap-4 focus:outline-none"
                                    :class="activeAccordion === {{ $index }} ? 'bg-[#1FA7A2]/5' : 'bg-white'">

                                <div class="w-10 h-10 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center flex-shrink-0 transition-all duration-300 group-hover:bg-[#1FA7A2] group-hover:scale-110 group-hover:rotate-6">
                                    <img src="{{ asset('brand/amr7/amr7-mark-light.svg') }}" class="h-5 w-auto opacity-90 transition-all duration-300 group-hover:brightness-0 group-hover:invert" alt="" aria-hidden="true">
                                </div>
                                
                                <span class="font-bold text-slate-800 flex-grow group-hover:text-[#1FA7A2] transition-colors">
                                    {{ $item['q'] }}
                                </span>

                                <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300"
                                   :class="activeAccordion === {{ $index }} ? 'rotate-180 text-[#1FA7A2]' : ''"></i>
                            </button>
                            
                            <div x-show="activeAccordion === {{ $index }}" x-collapse>
                                <div class="px-6 pb-6 pt-2">
                                    <div class="p-4 rounded-xl bg-slate-50 border-r-4 rtl:border-r-4 ltr:border-l-4 border-[#1FA7A2]">
                                        <p class="text-sm text-slate-600 leading-loose m-0">
                                            {{ $item['a'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- 3. Service Packages Grid Tab --}}
                <div x-show="activeTab === 'services'" style="display: none;"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="text-center mb-8">
                        <a href="{{ route('packages.index') }}" class="inline-block px-8 py-3 bg-[#1FA7A2] text-white rounded-full font-bold shadow-lg hover:bg-[#167F7B] hover:-translate-y-1 transition-all duration-300">
                            {{ __('faq.view_all_packages') }}
                        </a>
                    </div>
                    {{-- استدعاء ملف الجريد (تأكد أن هذا الملف أيضاً محدث لـ Tailwind أو سيعمل لكن بستايل قديم) --}}
                    @include('public.packages._grid', ['packages' => $packages ?? []])
                </div>

                {{-- 4. Site Orders Tab --}}
                <div x-show="activeTab === 'site-orders'" style="display: none;"
                     class="text-center py-12"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="inline-block max-w-2xl bg-white p-10 rounded-[2.5rem] border border-slate-100 shadow-sm">
                        <div class="mx-auto mb-6 w-20 h-20 bg-[#1FA7A2]/10 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-shopping-cart fa-3x text-[#1FA7A2]"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-4">{{ __('faq.site_orders_title') }}</h3>
                        <p class="text-slate-500 leading-loose">{{ __('faq.site_orders_desc') }}</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Contact Section --}}
    <section class="py-16 bg-white relative overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="relative bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white rounded-[2.5rem] p-10 md:p-16 text-center max-w-5xl mx-auto shadow-2xl shadow-[#1FA7A2]/20">
                
                <h2 class="text-3xl md:text-4xl font-bold mb-4">{{ __('faq.contact_title') }}</h2>
                <p class="text-white/70 text-lg mb-10">{{ __('faq.contact_subtitle') }}</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10 justify-center">
                    {{-- Email Box --}}
                    <div class="bg-white/10 backdrop-blur-sm p-8 rounded-3xl border border-white/20 hover:bg-white/20 transition-all duration-300">
                        <i class="fas fa-envelope-open-text text-white text-3xl mb-4"></i>
                        <h5 class="font-bold mb-2">{{ __('faq.email_support') }}</h5>
                        <a href="mailto:info@amr-7.sa" class="text-white font-bold text-lg hover:underline">info@amr-7.sa</a>
                    </div>
                    
                    {{-- Phone Box --}}
                    <div class="bg-white/10 backdrop-blur-sm p-8 rounded-3xl border border-white/20 hover:bg-white/20 transition-all duration-300">
                        <i class="fas fa-phone-volume text-white text-3xl mb-4"></i>
                        <h5 class="font-bold mb-2">{{ __('faq.direct_call') }}</h5>
                        <a href="tel:+966505336956" class="text-white font-bold text-lg hover:underline" dir="ltr">050 533 6956</a>
                    </div>
                </div>

                {{-- Chat Button --}}
                <a href="{{ route('contact.index') }}" class="inline-flex items-center px-8 py-4 bg-white text-[#1FA7A2] rounded-full font-bold shadow-lg hover:bg-slate-50 hover:-translate-y-1 transition-all duration-300">
                    <i class="fas fa-comments mx-2 rtl:order-last"></i> {{ __('faq.start_chat') }}
                </a>
            </div>
        </div>
    </section>

</div>

{{-- JSON-LD Schema --}}
<script type="application/ld+json">
{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection
