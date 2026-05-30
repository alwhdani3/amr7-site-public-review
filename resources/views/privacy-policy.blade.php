@extends('layouts.app')

@php
    if (class_exists(\Artesaos\SEOTools\Facades\SEOTools::class)) {
        \Artesaos\SEOTools\Facades\SEOTools::setTitle(__('privacy_seo_title'));
        \Artesaos\SEOTools\Facades\SEOTools::setDescription(__('privacy_seo_description'));
    }
@endphp

@section('content')
<div class="min-h-screen bg-slate-50 font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- Hero Section --}}
    <section class="relative pt-24 pb-20 overflow-hidden bg-gradient-to-b from-white to-slate-100">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-40 pointer-events-none" 
             style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 40px 40px;">
        </div>
        
        {{-- Ambient Blob --}}
        <div class="absolute top-[-50%] left-1/2 -translate-x-1/2 w-[600px] h-[600px] rounded-full bg-[#44BDB8]/15 blur-[100px] pointer-events-none"></div>

        <div class="container mx-auto px-4 relative z-10 text-center">
            {{-- Top Badge --}}
            <div class="mb-6 animate__animated animate__fadeInDown">
                <span class="inline-flex items-center px-6 py-2 rounded-full bg-[#1FA7A2]/10 text-[#1FA7A2] font-bold border border-[#1FA7A2]/20 shadow-sm">
                    <i class="fas fa-user-shield mx-2 rtl:order-last"></i> {{ __('digital_privacy_document') }}
                </span>
            </div>

            {{-- Title --}}
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-6 animate__animated animate__fadeInUp">
                {{ __('data_security_is') }} 
                <span class="bg-clip-text text-transparent bg-gradient-to-br from-[#1FA7A2] to-[#167F7B]">
                    {{ __('our_highest_commitment') }}
                </span>
            </h1>

            {{-- Description --}}
            <p class="text-lg text-slate-500 max-w-3xl mx-auto leading-loose mb-8 px-4 animate__animated animate__fadeInUp delay-100">
                {{ __('privacy_hero_description') }}
            </p>

            {{-- Secondary Badge --}}
            <div class="mb-8 animate__animated animate__fadeInUp delay-200">
                <span class="inline-flex items-center px-4 py-2 rounded-full bg-white border border-slate-200 text-slate-500 text-sm shadow-sm">
                    <i class="fas fa-gavel text-[#1FA7A2] mx-2 rtl:order-last"></i> {{ __('royal_decree_compliance') }}
                </span>
            </div>
        </div>
    </section>

    {{-- Content Section --}}
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-5xl mx-auto">
                
                {{-- Main Content Card --}}
                <div class="bg-white rounded-[2.5rem] p-8 md:p-12 border border-slate-200 shadow-xl animate__animated animate__fadeInUp">
                    
                    {{-- Card Header --}}
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-slate-900 mb-3">{{ __('data_collection_processing') }}</h2>
                        <p class="text-slate-500 text-lg mb-6">{{ __('data_collection_desc') }}</p>
                        
                        <div class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <i class="fas fa-fingerprint text-[#1FA7A2]"></i>
                            <span class="text-slate-800 font-bold text-sm">{{ __('data_purpose_statement') }}</span>
                        </div>
                    </div>

                    {{-- Security Section Title --}}
                    <div class="flex items-center gap-4 mb-8 pb-6 border-b border-slate-100">
                        <div class="w-12 h-12 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] text-xl flex-shrink-0">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900">{{ __('your_rights_security') }}</h3>
                    </div>

                    {{-- Features Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Feature 1 --}}
                        <div class="h-full p-6 rounded-3xl bg-slate-50 border border-slate-100 transition-all duration-300 hover:border-[#1FA7A2]/50 hover:bg-white hover:shadow-lg hover:-translate-y-1 group">
                            <i class="fas fa-user-lock text-[#1FA7A2] mb-4 text-3xl group-hover:scale-110 transition-transform duration-300"></i>
                            <h5 class="text-xl font-bold text-slate-900 mb-2">{{ __('data_sovereignty') }}</h5>
                            <p class="text-sm text-slate-500 leading-relaxed">{{ __('data_sovereignty_desc') }}</p>
                        </div>

                        {{-- Feature 2 --}}
                        <div class="h-full p-6 rounded-3xl bg-slate-50 border border-slate-100 transition-all duration-300 hover:border-[#1FA7A2]/50 hover:bg-white hover:shadow-lg hover:-translate-y-1 group">
                            <i class="fas fa-lock text-[#1FA7A2] mb-4 text-3xl group-hover:scale-110 transition-transform duration-300"></i>
                            <h5 class="text-xl font-bold text-slate-900 mb-2">{{ __('data_encryption') }}</h5>
                            <p class="text-sm text-slate-500 leading-relaxed">{{ __('data_encryption_desc') }}</p>
                        </div>

                        {{-- Feature 3 --}}
                        <div class="h-full p-6 rounded-3xl bg-slate-50 border border-slate-100 transition-all duration-300 hover:border-[#1FA7A2]/50 hover:bg-white hover:shadow-lg hover:-translate-y-1 group">
                            <i class="fas fa-server text-[#1FA7A2] mb-4 text-3xl group-hover:scale-110 transition-transform duration-300"></i>
                            <h5 class="text-xl font-bold text-slate-900 mb-2">{{ __('local_servers') }}</h5>
                            <p class="text-sm text-slate-500 leading-relaxed">{{ __('local_servers_desc') }}</p>
                        </div>

                        {{-- Feature 4 --}}
                        <div class="h-full p-6 rounded-3xl bg-slate-50 border border-slate-100 transition-all duration-300 hover:border-[#1FA7A2]/50 hover:bg-white hover:shadow-lg hover:-translate-y-1 group">
                            <i class="fas fa-handshake-slash text-[#1FA7A2] mb-4 text-3xl group-hover:scale-110 transition-transform duration-300"></i>
                            <h5 class="text-xl font-bold text-slate-900 mb-2">{{ __('privacy_commitment') }}</h5>
                            <p class="text-sm text-slate-500 leading-relaxed">{{ __('privacy_commitment_desc') }}</p>
                        </div>
                    </div>

                    {{-- Data Retention Footer --}}
                    <div class="mt-12 text-center">
                        <div class="inline-flex items-center px-8 py-3 rounded-full bg-[#1FA7A2]/5 text-[#1FA7A2] font-bold border border-[#1FA7A2]/20 text-sm">
                            <i class="fas fa-history mx-2 rtl:order-last"></i> {{ __('data_retention_policy') }}
                        </div>
                    </div>
                </div>

                {{-- Contact Strip --}}
                <div class="relative mt-12 bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white rounded-[2.5rem] p-10 md:p-16 text-center shadow-2xl shadow-[#1FA7A2]/20 animate__animated animate__fadeInUp">
                    <h3 class="text-2xl md:text-3xl font-bold mb-4">{{ __('privacy_inquiry_title') }}</h3>
                    <p class="text-white/70 mb-8 max-w-2xl mx-auto">{{ __('privacy_team_availability') }}</p>
                    
                    <div class="flex flex-wrap justify-center gap-6 mb-8">
                        {{-- Email --}}
                        <div class="bg-white/10 backdrop-blur-sm px-6 py-4 rounded-2xl border border-white/20 hover:bg-white/20 transition-colors">
                            <i class="fas fa-envelope-open mb-2 block text-[#44BDB8]"></i>
                            <a href="mailto:info@amr-7.sa" class="text-white font-bold hover:underline">info@amr-7.sa</a>
                        </div>
                        {{-- Phone --}}
                        <div class="bg-white/10 backdrop-blur-sm px-6 py-4 rounded-2xl border border-white/20 hover:bg-white/20 transition-colors">
                            <i class="fas fa-phone-alt mb-2 block text-[#44BDB8]"></i>
                            <a href="tel:+966505336956" class="text-white font-bold hover:underline" dir="ltr">050 533 6956</a>
                        </div>
                    </div>

                    <a href="{{ route('contact.index') }}" class="inline-flex items-center px-8 py-4 bg-white text-[#1FA7A2] rounded-full font-bold shadow-lg hover:bg-slate-50 hover:-translate-y-1 transition-all duration-300">
                        <i class="fas fa-headset mx-2 rtl:order-last"></i> {{ __('contact_privacy_team') }}
                    </a>
                </div>

            </div>
        </div>
    </section>

</div>
@endsection
