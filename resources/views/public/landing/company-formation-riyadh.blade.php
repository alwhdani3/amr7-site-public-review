@extends('layouts.app')

@section('title', __('meta_title_formation'))

@section('content')
@if(!empty($officialPageSchema))
    <script type="application/ld+json">{!! json_encode($officialPageSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif

@if(!empty($officialFaqSchema))
    <script type="application/ld+json">{!! json_encode($officialFaqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif

<div class="min-h-screen bg-slate-50 font-['Tajawal'] relative overflow-x-hidden" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#1FA7A2]/5 rounded-full blur-3xl mix-blend-multiply opacity-70 animate-blob"></div>
        <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-teal-200/10 rounded-full blur-3xl mix-blend-multiply opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-32 left-20 w-[600px] h-[600px] bg-slate-200/20 rounded-full blur-3xl mix-blend-multiply opacity-70 animate-blob animation-delay-4000"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:32px_32px] opacity-40"></div>
    </div>

    <a href="https://wa.me/966505336956" target="_blank"
       class="fixed bottom-8 z-50 w-16 h-16 bg-[#25D366] text-white rounded-full flex items-center justify-center text-3xl shadow-lg shadow-green-500/30 hover:scale-110 transition-transform duration-300 animate-bounce-slow rtl:right-8 ltr:left-8" rel="noopener noreferrer">
        <i class="fab fa-whatsapp"></i>
    </a>

    <section class="relative z-10 pt-32 pb-20 lg:pt-40 lg:pb-28">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

                <div class="text-center lg:text-start rtl:lg:text-right ltr:lg:text-left animate__animated animate__fadeInRight">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#1FA7A2]/5 border border-[#1FA7A2]/10 text-[#1FA7A2] text-sm font-bold mb-6">
                        <i class="fas fa-check-circle"></i> {{ __('hero_badge_text') }}
                    </div>

                    <h1 class="text-4xl lg:text-6xl font-black text-slate-900 mb-6 leading-tight">
                        {{ __('hero_main_title') }}
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#1FA7A2] to-teal-500">
                            {{ __('formation') }}
                        </span>
                    </h1>

                    <p class="text-lg text-slate-600 mb-8 leading-relaxed">
                        {{ __('hero_description_start') }}
                        <strong class="text-[#1FA7A2]">{{ __('hero_desc_bold_1') }}</strong>,
                        {{ __('hero_desc_middle') }}
                        <strong class="text-[#1FA7A2]">{{ __('hero_desc_bold_2') }}</strong>.
                        {{ __('hero_desc_end') }}
                    </p>

                    <div class="flex flex-wrap justify-center lg:justify-start gap-4">
                        <a href="#form-section"
                           class="px-8 py-4 rounded-full bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold text-lg shadow-lg hover:shadow-teal-900/20 hover:-translate-y-1 transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-file-signature"></i> {{ __('btn_request_license') }}
                        </a>
                        <a href="https://wa.me/966505336956" target="_blank"
                           class="px-8 py-4 rounded-full bg-white text-[#1FA7A2] border-2 border-[#1FA7A2] font-bold text-lg hover:bg-[#1FA7A2] hover:text-white transition-all duration-300 flex items-center gap-2" rel="noopener noreferrer">
                            <i class="fas fa-headset"></i> {{ __('btn_phone_consultation') }}
                        </a>
                    </div>
                </div>

                <div class="relative animate__animated animate__fadeInLeft animate__delay-1s">
                    <div class="relative bg-white/60 backdrop-blur-xl border border-white/50 rounded-[2.5rem] p-8 shadow-2xl shadow-slate-200/50 text-center">
                        <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.png') }}" alt="Amr Seven Business Solutions" class="h-24 mx-auto mb-6 object-contain">

                        <h3 class="text-2xl font-extrabold text-slate-800 mb-2">{{ __('side_card_title') }}</h3>
                        <p class="text-slate-500 mb-8">{{ __('side_card_subtitle') }}</p>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <div class="text-3xl font-black text-emerald-500 mb-1">+500</div>
                                <div class="text-xs font-bold text-slate-400">{{ __('stat_companies') }}</div>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <div class="text-3xl font-black text-amber-500 mb-1">100%</div>
                                <div class="text-xs font-bold text-slate-400">{{ __('stat_acceptance') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-teal-400/20 rounded-full blur-2xl animate-pulse"></div>
                    <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-400/20 rounded-full blur-2xl animate-pulse"></div>
                </div>

            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 relative z-10">
        @include('services.partials.official-content', ['officialContent' => $officialContent ?? null])
    </div>

    <section class="py-20 relative z-10">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16 max-w-3xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4">{{ __('services_section_title') }}</h2>
                <p class="text-slate-500 text-lg">{{ __('services_section_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach([
                    ['icon' => 'passport', 'title' => 'service_misa_title', 'desc' => 'service_misa_desc', 'color' => 'bg-blue-50 text-blue-600'],
                    ['icon' => 'file-contract', 'title' => 'service_cr_title', 'desc' => 'service_cr_desc', 'color' => 'bg-teal-50 text-teal-600'],
                    ['icon' => 'building-columns', 'title' => 'service_bank_title', 'desc' => 'service_bank_desc', 'color' => 'bg-purple-50 text-purple-600'],
                    ['icon' => 'folder-open', 'title' => 'service_gov_title', 'desc' => 'service_gov_desc', 'color' => 'bg-amber-50 text-amber-600'],
                    ['icon' => 'id-card', 'title' => 'service_residency_title', 'desc' => 'service_residency_desc', 'color' => 'bg-rose-50 text-rose-600'],
                    ['icon' => 'chart-pie', 'title' => 'service_feasibility_title', 'desc' => 'service_feasibility_desc', 'color' => 'bg-indigo-50 text-indigo-600']
                ] as $service)
                    <div class="group bg-white rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 border border-slate-100">
                        <div class="w-14 h-14 rounded-2xl {{ $service['color'] }} flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                            <i class="fas fa-{{ $service['icon'] }}"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">{{ __($service['title']) }}</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">{{ __($service['desc']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 bg-white relative z-10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">

                <div class="lg:col-span-5 sticky top-28">
                    <h2 class="text-3xl font-extrabold text-slate-900 mb-6">{{ __('structures_title') }}</h2>
                    <p class="text-slate-500 text-lg mb-8 leading-relaxed">{{ __('structures_desc') }}</p>

                    <div class="bg-teal-50 border border-teal-100 rounded-3xl p-6 flex gap-4">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-[#1FA7A2] text-xl shrink-0 shadow-sm">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 mb-1">{{ __('amr7_tip_title') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('amr7_tip_text') }}</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-4" x-data="{ active: 1 }">
                    @foreach([
                        ['id' => 1, 'icon' => 'building', 'title' => 'struct_llc_title', 'sub' => 'struct_llc_sub', 'desc' => 'struct_llc_desc'],
                        ['id' => 2, 'icon' => 'chart-line', 'title' => 'struct_sjsc_title', 'sub' => 'struct_sjsc_sub', 'desc' => 'struct_sjsc_desc'],
                        ['id' => 3, 'icon' => 'user-tie', 'title' => 'struct_one_person_title', 'sub' => 'struct_one_person_sub', 'desc' => 'struct_one_person_desc'],
                        ['id' => 4, 'icon' => 'city', 'title' => 'struct_rhq_title', 'sub' => 'struct_rhq_sub', 'desc' => 'struct_rhq_desc'],
                        ['id' => 5, 'icon' => 'globe', 'title' => 'struct_branch_title', 'sub' => 'struct_branch_sub', 'desc' => 'struct_branch_desc'],
                        ['id' => 6, 'icon' => 'user-doctor', 'title' => 'struct_pro_title', 'sub' => 'struct_pro_sub', 'desc' => 'struct_pro_desc'],
                    ] as $struct)
                        <div class="bg-white border rounded-2xl overflow-hidden transition-all duration-300"
                             :class="active === {{ $struct['id'] }} ? 'border-[#1FA7A2] shadow-md ring-1 ring-[#1FA7A2]/20' : 'border-slate-200'">

                            <button @click="active = {{ $struct['id'] }}"
                                    class="w-full flex items-center gap-4 p-5 text-start transition-colors hover:bg-slate-50">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-colors"
                                     :class="active === {{ $struct['id'] }} ? 'bg-[#1FA7A2] text-white' : 'bg-slate-100 text-slate-500'">
                                    <i class="fas fa-{{ $struct['icon'] }}"></i>
                                </div>
                                <div class="flex-grow">
                                    <div class="font-bold text-slate-800 text-lg">{{ __($struct['title']) }}</div>
                                    <div class="text-xs text-slate-400 font-medium">{{ __($struct['sub']) }}</div>
                                </div>
                                <i class="fas fa-chevron-down text-slate-300 transition-transform duration-300"
                                   :class="active === {{ $struct['id'] }} ? 'rotate-180 text-[#1FA7A2]' : ''"></i>
                            </button>

                            <div x-show="active === {{ $struct['id'] }}" x-collapse>
                                <div class="p-5 pt-0 text-slate-600 leading-relaxed border-t border-dashed border-slate-100 mt-2">
                                    <div class="pt-4">{{ __($struct['desc']) }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </section>

    <section class="py-20 relative z-10">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-slate-900">{{ __('faq_title') }}</h2>
            </div>

            <div class="max-w-3xl mx-auto space-y-4" x-data="{ faqActive: null }">
                @foreach([
                    ['id' => 1, 'q' => 'faq_q1', 'a' => 'faq_a1'],
                    ['id' => 2, 'q' => 'faq_q2', 'a' => 'faq_a2'],
                    ['id' => 3, 'q' => 'faq_q3', 'a' => 'faq_a3'],
                    ['id' => 4, 'q' => 'faq_q4', 'a' => 'faq_a4'],
                ] as $faq)
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all duration-300"
                         :class="faqActive === {{ $faq['id'] }} ? 'shadow-md border-[#1FA7A2]/50' : ''">
                        <button @click="faqActive = faqActive === {{ $faq['id'] }} ? null : {{ $faq['id'] }}"
                                class="w-full flex justify-between items-center p-5 text-start font-bold text-slate-800 hover:text-[#1FA7A2] transition-colors">
                            <span>{{ __($faq['q']) }}</span>
                            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-transform duration-300"
                                  :class="faqActive === {{ $faq['id'] }} ? 'rotate-180 bg-teal-50 text-[#1FA7A2]' : ''">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </button>
                        <div x-show="faqActive === {{ $faq['id'] }}" x-collapse>
                            <div class="px-5 pb-5 text-slate-500 leading-relaxed">
                                {{ __($faq['a']) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 bg-white relative z-10" id="form-section">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-slate-100 p-8 md:p-12 relative overflow-hidden">

                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#1FA7A2] to-teal-400"></div>

                    <div class="text-center mb-10">
                        <h2 class="text-3xl font-black text-slate-900 mb-2">{{ __('form_title') }}</h2>
                        <p class="text-slate-500">{{ __('form_subtitle') }}</p>
                    </div>

                    <livewire:public.landing-company-formation-form lazy :key="'landing-company-formation-form'" />
                </div>
            </div>
        </div>
    </section>

</div>

@php
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "ProfessionalService",
        "name" => "Amr Seven Business Solutions",
        "image" => asset('brand/amr7/amr7-logo-lockup-light.png'),
        "description" => __('meta_description_formation'),
        "address" => [
            "@type" => "PostalAddress",
            "addressLocality" => "Riyadh",
            "addressCountry" => "SA",
        ],
        "priceRange" => "$$",
    ];
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@push('scripts')
<script>
(function () {
    const CONFIG = {
        revealClass: 'reveal',
        activeClass: 'active',
        revealThreshold: 0.15,
        styleId: 'reveal-style'
    };

    function ensureRevealStyle() {
        if (document.getElementById(CONFIG.styleId)) return;

        const style = document.createElement('style');
        style.id = CONFIG.styleId;
        style.textContent = '.reveal{opacity:0;transform:translateY(30px);transition:all .8s cubic-bezier(.5,0,0,1)}.reveal.active{opacity:1;transform:translateY(0)}';
        document.head.appendChild(style);
    }

    function initScrollReveal() {
        const elements = document.querySelectorAll('.' + CONFIG.revealClass);
        if (!elements.length) return;

        ensureRevealStyle();

        if (window.__amr7RevealObserver && typeof window.__amr7RevealObserver.disconnect === 'function') {
            window.__amr7RevealObserver.disconnect();
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add(CONFIG.activeClass);
                observer.unobserve(entry.target);
            });
        }, { threshold: CONFIG.revealThreshold });

        window.__amr7RevealObserver = observer;

        elements.forEach((el) => {
            if (el.classList.contains(CONFIG.activeClass)) return;
            observer.observe(el);
        });
    }

    function init() {
        initScrollReveal();
    }

    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        init();
    } else {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    }

    document.addEventListener('livewire:navigated', init);
})();
</script>
@endpush

@endsection
