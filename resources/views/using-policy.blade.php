@extends('layouts.app')

@php
    if (class_exists(\Artesaos\SEOTools\Facades\SEOTools::class)) {
        \Artesaos\SEOTools\Facades\SEOTools::setTitle(__('terms.seo_title'));
        \Artesaos\SEOTools\Facades\SEOTools::setDescription(__('terms.seo_description'));
    }
@endphp

@section('content')
@php
    $isAr = app()->getLocale() === 'ar';
    $dir  = $isAr ? 'rtl' : 'ltr';
    $supportPhoneDisplay = '050 533 6956';
    $supportPhoneTel     = '+966505336956';
    $supportWhatsapp     = '966505336956';
@endphp

<div class="min-h-screen bg-slate-50 font-['Tajawal'] overflow-hidden text-slate-900" dir="{{ $dir }}">

    {{-- HERO SECTION --}}
    <section class="relative pt-20 pb-20 bg-gradient-to-b from-white to-slate-50 overflow-hidden">
        {{-- خلفية شبكية خفيفة --}}
        <div class="absolute inset-0 opacity-40 pointer-events-none" 
             style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 40px 40px;">
        </div>
        
        {{-- فقاعة لونية جمالية --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-[#1FA7A2]/5 rounded-full blur-3xl pointer-events-none -mt-40"></div>

        <div class="container mx-auto px-4 relative z-10 text-center pt-8">
            {{-- الشارة العلوية --}}
            <div class="mb-6">
                <span class="inline-flex items-center gap-2 bg-[#1FA7A2]/10 text-[#1FA7A2] px-6 py-2 rounded-full font-bold text-sm border border-[#1FA7A2]/20 shadow-sm animate__animated animate__fadeInDown">
                    <i class="fas fa-file-shield {{ $isAr ? 'ms-2' : 'me-2' }}"></i> 
                    {{ __('terms.hero.badge') }}
                </span>
            </div>

            {{-- العنوان الرئيسي --}}
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-6 leading-tight animate__animated animate__fadeInUp">
                {{ __('terms.hero.title_prefix') }}
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#1FA7A2] to-[#167F7B]">{{ __('terms.hero.title_highlight') }}</span>
                {{ __('terms.hero.title_suffix') }}
            </h1>

            {{-- الوصف --}}
            <div class="max-w-4xl mx-auto mb-8 px-4 text-slate-500 text-lg leading-loose animate__animated animate__fadeInUp delay-100">
                {!! __('terms.hero.subtitle_html') !!}
            </div>

            {{-- أزرار التحكم --}}
            <div class="flex flex-wrap gap-3 justify-center mt-6 animate__animated animate__fadeInUp delay-200">
                <button type="button" 
                        class="px-6 py-3 rounded-full bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center gap-2" 
                        onclick="window.print()">
                    <i class="fas fa-print {{ $isAr ? 'ms-2' : 'me-2' }}"></i> 
                    {{ __('terms.actions.print') }}
                </button>

                <button type="button" 
                        id="copyLinkBtn"
                        class="px-6 py-3 rounded-full border-2 border-[#1FA7A2] text-[#1FA7A2] font-bold bg-transparent hover:bg-[#1FA7A2] hover:text-white transition-all duration-300 flex items-center gap-2">
                    <i class="fas fa-link {{ $isAr ? 'ms-2' : 'me-2' }}"></i> 
                    {{ __('terms.actions.copy_link') }}
                </button>

                <span class="inline-flex items-center px-4 text-slate-500 text-sm {{ $isAr ? 'border-r-2' : 'border-l-2' }} border-slate-200">
                    <i class="fas fa-clock text-[#1FA7A2] {{ $isAr ? 'ms-2' : 'me-2' }}"></i> 
                    {{ __('terms.meta.last_updated') }}: 
                    <span class="font-bold mx-1 text-slate-900">{{ __('terms.meta.last_updated_value') }}</span>
                </span>
            </div>

            {{-- جدول المحتويات (TOC) --}}
            <div class="mt-12 max-w-4xl mx-auto">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 text-center">
                    <div class="text-slate-900 font-bold mb-4 flex items-center justify-center gap-2">
                        <i class="fas fa-list-check text-[#1FA7A2] {{ $isAr ? 'ms-2' : 'me-2' }}"></i> 
                        {{ __('terms.toc.title') }}
                    </div>
                    <div class="flex flex-wrap gap-3 justify-center">
                        <a class="text-[#1FA7A2] border border-[#1FA7A2]/20 px-5 py-2 rounded-full text-sm font-bold bg-white hover:bg-[#1FA7A2]/5 hover:-translate-y-1 hover:text-[#1FA7A2] transition-all duration-300 scroll-smooth" href="#section-company">{{ __('terms.toc.company') }}</a>
                        <a class="text-[#1FA7A2] border border-[#1FA7A2]/20 px-5 py-2 rounded-full text-sm font-bold bg-white hover:bg-[#1FA7A2]/5 hover:-translate-y-1 hover:text-[#1FA7A2] transition-all duration-300 scroll-smooth" href="#section-restrictions">{{ __('terms.toc.restrictions') }}</a>
                        <a class="text-[#1FA7A2] border border-[#1FA7A2]/20 px-5 py-2 rounded-full text-sm font-bold bg-white hover:bg-[#1FA7A2]/5 hover:-translate-y-1 hover:text-[#1FA7A2] transition-all duration-300 scroll-smooth" href="#section-rights">{{ __('terms.toc.rights') }}</a>
                        <a class="text-[#1FA7A2] border border-[#1FA7A2]/20 px-5 py-2 rounded-full text-sm font-bold bg-white hover:bg-[#1FA7A2]/5 hover:-translate-y-1 hover:text-[#1FA7A2] transition-all duration-300 scroll-smooth" href="#section-contact">{{ __('terms.toc.contact') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- COMPANY SECTION --}}
    <section class="py-16 bg-slate-50" id="section-company">
        <div class="container mx-auto px-4 text-center">
            <div class="max-w-5xl mx-auto animate-on-scroll">
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-lg transition-shadow duration-500">
                    <div class="relative z-10">
                        <div class="w-24 h-24 bg-[#1FA7A2]/10 rounded-3xl flex items-center justify-center text-[#1FA7A2] text-4xl mb-6 mx-auto">
                            <i class="fas fa-building-circle-check"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 mb-6">{{ __('terms.company.title') }}</h3>
                        <div class="text-slate-600 leading-loose text-lg px-4 md:px-10">
                            {!! __('terms.company.desc_html') !!}
                        </div>

                        <div class="mt-8">
                            <button class="text-[#1FA7A2] font-bold text-sm hover:underline flex items-center justify-center gap-2 cursor-pointer mx-auto bg-transparent border-0" onclick="copySectionLink('section-company')">
                                <i class="fas fa-link {{ $isAr ? 'ms-1' : 'me-1' }}"></i> 
                                {{ __('terms.actions.copy_section_link') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- RESTRICTIONS SECTION --}}
    <section class="py-16 bg-white relative" id="section-restrictions">
        <div class="container mx-auto px-4 py-8">
            <div class="text-center mb-12 animate-on-scroll">
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-4">
                    {{ __('terms.restrictions.title_prefix') }}
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#1FA7A2] to-[#167F7B]">{{ __('terms.restrictions.title_highlight') }}</span>
                </h2>
                <p class="text-slate-500 text-lg mt-3">{{ __('terms.restrictions.subtitle') }}</p>
            </div>

            @php
                $restrictions = [
                    ['icon' => 'fa-virus-slash', 'title' => __('terms.restrictions.items.security.title'), 'desc' => __('terms.restrictions.items.security.desc')],
                    ['icon' => 'fa-user-secret', 'title' => __('terms.restrictions.items.identity.title'), 'desc' => __('terms.restrictions.items.identity.desc')],
                    ['icon' => 'fa-ban', 'title' => __('terms.restrictions.items.reputation.title'), 'desc' => __('terms.restrictions.items.reputation.desc')],
                    ['icon' => 'fa-link-slash', 'title' => __('terms.restrictions.items.links.title'), 'desc' => __('terms.restrictions.items.links.desc')],
                    ['icon' => 'fa-database', 'title' => __('terms.restrictions.items.data.title'), 'desc' => __('terms.restrictions.items.data.desc')],
                    ['icon' => 'fa-gavel', 'title' => __('terms.restrictions.items.compliance.title'), 'desc' => __('terms.restrictions.items.compliance.desc')],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($restrictions as $res)
                    <div class="animate-on-scroll h-full">
                        <div class="bg-slate-50 p-6 rounded-[1.5rem] border border-slate-100 h-full hover:border-[#1FA7A2] hover:-translate-y-2 hover:shadow-lg transition-all duration-300 group">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-[#1FA7A2]/10 text-[#1FA7A2] rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm group-hover:bg-[#1FA7A2] group-hover:text-white transition-colors">
                                    <i class="fas {{ $res['icon'] }}"></i>
                                </div>
                                <h5 class="text-slate-900 font-bold text-sm">{{ $res['title'] }}</h5>
                            </div>
                            <p class="text-slate-500 text-sm leading-loose">{{ $res['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-10">
                <button class="text-[#1FA7A2] font-bold text-sm hover:underline flex items-center justify-center gap-2 cursor-pointer mx-auto bg-transparent border-0" onclick="copySectionLink('section-restrictions')">
                    <i class="fas fa-link {{ $isAr ? 'ms-1' : 'me-1' }}"></i> 
                    {{ __('terms.actions.copy_section_link') }}
                </button>
            </div>
        </div>
    </section>

    {{-- RIGHTS SECTION --}}
    <section class="py-16 bg-slate-50" id="section-rights">
        <div class="container mx-auto px-4 py-8 text-center animate-on-scroll">
            <div class="max-w-5xl mx-auto">
                {{-- Quote Box --}}
                <div class="bg-white p-8 md:p-12 rounded-[2.5rem] shadow-sm mb-10 border border-slate-100 relative overflow-hidden">
                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-center gap-6">
                        <i class="fas fa-copyright text-[#1FA7A2] text-5xl"></i>
                        <h4 class="text-xl md:text-2xl font-bold text-slate-900 leading-normal">
                            {!! __('terms.rights.quote_html') !!}
                        </h4>
                    </div>
                </div>

                {{-- Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 justify-center">
                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 hover:-translate-y-1 h-full">
                        <div class="w-14 h-14 bg-[#1FA7A2]/10 text-[#1FA7A2] rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-balance-scale text-xl"></i>
                        </div>
                        <h4 class="text-slate-900 font-bold mb-3">{{ __('terms.rights.jurisdiction.title') }}</h4>
                        <p class="text-slate-500 text-sm leading-loose px-2">{!! __('terms.rights.jurisdiction.desc_html') !!}</p>
                    </div>

                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 hover:-translate-y-1 h-full">
                        <div class="w-14 h-14 bg-[#1FA7A2]/10 text-[#1FA7A2] rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shield-halved text-xl"></i>
                        </div>
                        <h4 class="text-slate-900 font-bold mb-3">{{ __('terms.rights.disclaimer.title') }}</h4>
                        <p class="text-slate-500 text-sm leading-loose px-2">{{ __('terms.rights.disclaimer.desc') }}</p>
                    </div>
                </div>

                <div class="bg-[#1FA7A2]/5 px-8 py-4 rounded-full border border-[#1FA7A2]/10 mt-10 inline-block">
                    <p class="text-[#1FA7A2] font-bold text-sm flex items-center gap-2">
                        <i class="fas fa-language {{ $isAr ? 'ms-2' : 'me-2' }}"></i> 
                        {{ __('terms.rights.language_notice') }}
                    </p>
                </div>

                <div class="mt-6">
                    <button class="text-[#1FA7A2] font-bold text-sm hover:underline flex items-center justify-center gap-2 cursor-pointer mx-auto bg-transparent border-0" onclick="copySectionLink('section-rights')">
                        <i class="fas fa-link {{ $isAr ? 'ms-1' : 'me-1' }}"></i> 
                        {{ __('terms.actions.copy_section_link') }}
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- CONTACT SECTION --}}
    <section class="py-16 bg-white overflow-hidden" id="section-contact">
        <div class="container mx-auto px-4 py-8">
            <div class="bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white rounded-[2.5rem] p-8 md:p-12 text-center mx-auto max-w-4xl shadow-xl relative overflow-hidden">
                {{-- Texture Overlay --}}
                <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                
                <div class="relative z-10">
                    <h3 class="text-2xl font-black mb-3">{{ __('terms.contact.title') }}</h3>
                    <p class="text-teal-100 mb-10 text-sm">{{ __('terms.contact.subtitle') }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10 max-w-2xl mx-auto">
                        <div class="bg-white/10 p-6 rounded-2xl border border-white/10 backdrop-blur-sm hover:bg-white/20 transition-colors">
                            <i class="fas fa-envelope-open-text text-white text-3xl mb-4"></i>
                            <h6 class="text-teal-100 text-xs mb-1 uppercase tracking-wider">{{ __('terms.contact.legal_email_label') }}</h6>
                            <a href="mailto:info@amr-7.sa" class="text-white font-bold hover:underline">info@amr-7.sa</a>
                        </div>

                        <div class="bg-white/10 p-6 rounded-2xl border border-white/10 backdrop-blur-sm hover:bg-white/20 transition-colors">
                            <i class="fas fa-phone-volume text-white text-3xl mb-4"></i>
                            <h6 class="text-teal-100 text-xs mb-1 uppercase tracking-wider">{{ __('terms.contact.phone_label') }}</h6>
                            <a href="tel:{{ $supportPhoneTel }}" class="text-white font-bold hover:underline font-mono" dir="ltr">
                                {{ $supportPhoneDisplay }}
                            </a>
                        </div>
                    </div>

                    {{-- نموذج تواصل لايف واير --}}
                    <div id="contact-form" class="bg-white p-6 md:p-8 rounded-3xl text-slate-900 shadow-lg text-start">
                        <livewire:contact-quick-form />
                    </div>

                    <div class="mt-8">
                        <button class="text-white/70 text-sm hover:text-white transition-colors flex items-center justify-center gap-2 cursor-pointer mx-auto bg-transparent border-0" onclick="copySectionLink('section-contact')">
                            <i class="fas fa-link {{ $isAr ? 'ms-1' : 'me-1' }}"></i> 
                            {{ __('terms.actions.copy_section_link') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

{{-- FLOATING BUTTONS --}}
<div class="fixed bottom-6 z-50 flex flex-col gap-3 {{ $isAr ? 'right-6' : 'left-6' }}" aria-label="{{ $isAr ? 'أزرار تواصل سريعة' : 'Quick contact actions' }}">
    <a href="tel:{{ $supportPhoneTel }}" class="w-14 h-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-lg hover:-translate-y-1 transition-transform duration-300" aria-label="Call">
        <i class="fas fa-phone-alt text-xl"></i>
    </a>
    <a href="https://wa.me/{{ $supportWhatsapp }}" target="_blank" class="w-14 h-14 rounded-2xl bg-[#10b981] text-white flex items-center justify-center shadow-lg hover:-translate-y-1 transition-transform duration-300" aria-label="WhatsApp">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>
</div>

{{-- SCRIPTS --}}
<script>
    function copySectionLink(id) {
        const url = `${window.location.origin}${window.location.pathname}#${id}`;
        navigator.clipboard.writeText(url).then(() => {
            // يمكنك إضافة SweetAlert هنا إذا أردت
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('copyLinkBtn');
        if (btn) {
            btn.addEventListener('click', () => {
                navigator.clipboard.writeText(window.location.href);
            });
        }
        
        // تأثيرات الظهور عند التمرير (بسيط وخفيف)
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                    observer.unobserve(entry.target);
                }
            });
        });

        document.querySelectorAll('.animate-on-scroll').forEach((el) => {
            observer.observe(el);
        });
    });
</script>
@endsection
