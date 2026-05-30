@extends('layouts.app')

@section('content')

{{-- الحاوية الرئيسية: فليكس لضمان الفوتر في الأسفل ومسافة علوية للهيدر --}}
<div class="min-h-screen bg-slate-50 font-['Tajawal'] pt-28 pb-20 relative flex flex-col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- خلفية جمالية --}}
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#1FA7A2]/5 blur-[100px] rounded-full"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-amber-500/5 blur-[100px] rounded-full"></div>
        {{-- نمط الشبكة الخفيف --}}
        <div class="absolute inset-0" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 40px 40px; opacity: 0.3;"></div>
    </div>

    {{-- Hero Section --}}
    <div class="container mx-auto px-4 relative z-10 text-center mb-12">
        <div class="mb-4">
            <span class="inline-flex items-center gap-2 bg-[#1FA7A2]/10 text-[#1FA7A2] border border-[#1FA7A2]/20 px-6 py-2 rounded-full font-bold text-sm shadow-sm animate__animated animate__fadeInDown">
                <i class="fas fa-cog"></i> {{ __('account_settings') }}
            </span>
        </div>
        <h1 class="text-3xl md:text-5xl font-black text-slate-900 mb-4 animate__animated animate__fadeInUp">
            {{ __('settings_title') }} 
            <span class="bg-clip-text text-transparent bg-gradient-to-br from-[#1FA7A2] to-[#167F7B]">
                {{ __('settings_highlight') }}
            </span>
        </h1>
        <p class="text-lg text-slate-500 max-w-2xl mx-auto leading-relaxed animate__animated animate__fadeInUp animate__delay-1s">
            {{ __('settings_description') }}
        </p>
    </div>

    {{-- Main Content (Grid Layout + Alpine Tabs) --}}
    <div class="container mx-auto px-4 max-w-6xl relative z-10 flex-grow" x-data="{ activeTab: 'profile' }">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- Sidebar Navigation (3 columns) --}}
            <div class="lg:col-span-3 w-full animate__animated animate__fadeInLeft">
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-200 lg:sticky lg:top-32">
                    <nav class="flex flex-col gap-2">
                        <button @click="activeTab = 'profile'" 
                                :class="activeTab === 'profile' ? 'bg-[#1FA7A2] text-white shadow-lg shadow-[#1FA7A2]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'"
                                class="flex items-center gap-3 px-5 py-3 rounded-xl font-bold text-sm transition-all duration-300 w-full text-start">
                            <i class="fas fa-user-circle text-lg"></i> {{ __('profile_info') }}
                        </button>
                        
                        <button @click="activeTab = 'security'" 
                                :class="activeTab === 'security' ? 'bg-[#1FA7A2] text-white shadow-lg shadow-[#1FA7A2]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'"
                                class="flex items-center gap-3 px-5 py-3 rounded-xl font-bold text-sm transition-all duration-300 w-full text-start">
                            <i class="fas fa-shield-alt text-lg"></i> {{ __('security_password') }}
                        </button>
                        
                        <button @click="activeTab = 'notifications'" 
                                :class="activeTab === 'notifications' ? 'bg-[#1FA7A2] text-white shadow-lg shadow-[#1FA7A2]/30' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'"
                                class="flex items-center gap-3 px-5 py-3 rounded-xl font-bold text-sm transition-all duration-300 w-full text-start">
                            <i class="fas fa-bell text-lg"></i> {{ __('notifications') }}
                        </button>
                    </nav>
                </div>
            </div>

            {{-- Content Area (9 columns) --}}
            <div class="lg:col-span-9 w-full animate__animated animate__fadeInUp animate__delay-1s">
                
                {{-- 1. Profile Tab --}}
                <div x-show="activeTab === 'profile'" x-transition.opacity>
                    <div class="bg-white p-6 md:p-10 rounded-[2rem] shadow-sm border border-slate-200">
                        <h4 class="text-xl font-bold text-slate-900 mb-6 pb-4 border-b border-slate-100">{{ __('personal_information') }}</h4>
                        
                        <form action="#" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @csrf
                            
                            {{-- Full Name --}}
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wide">{{ __('full_name') }}</label>
                                <div class="group flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 transition-all duration-300 focus-within:bg-white focus-within:border-[#1FA7A2] focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                                    <i class="fas fa-user text-[#1FA7A2]"></i>
                                    <input type="text" name="name" value="{{ auth()->user()->name }}" class="w-full bg-transparent border-none focus:ring-0 py-3 px-3 text-slate-700 text-sm font-semibold">
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wide">{{ __('email_address') }}</label>
                                <div class="group flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 transition-all duration-300 focus-within:bg-white focus-within:border-[#1FA7A2] focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                                    <i class="fas fa-envelope text-[#1FA7A2]"></i>
                                    <input type="email" name="email" value="{{ auth()->user()->email }}" class="w-full bg-transparent border-none focus:ring-0 py-3 px-3 text-slate-700 text-sm font-semibold">
                                </div>
                            </div>

                            {{-- Mobile --}}
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wide">{{ __('mobile_number') }}</label>
                                <div class="group flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 transition-all duration-300 focus-within:bg-white focus-within:border-[#1FA7A2] focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                                    <i class="fas fa-mobile-alt text-[#1FA7A2]"></i>
                                    <input type="tel" name="phone" placeholder="05xxxxxxxx" class="w-full bg-transparent border-none focus:ring-0 py-3 px-3 text-slate-700 text-sm font-semibold text-end" dir="ltr">
                                </div>
                            </div>

                            <div class="col-span-1 md:col-span-2 mt-4 text-end">
                                <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-[#1FA7A2] hover:bg-[#167F7B] text-white font-bold rounded-full shadow-lg transition-all hover:-translate-y-1">
                                    {{ __('save_changes') }} <i class="fas fa-save"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- 2. Security Tab --}}
                <div x-show="activeTab === 'security'" x-transition.opacity style="display: none;">
                    <div class="bg-white p-6 md:p-10 rounded-[2rem] shadow-sm border border-slate-200">
                        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                            <h4 class="text-xl font-bold text-slate-900">{{ __('two_factor_auth') }}</h4>
                            <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-600 border border-green-100 px-4 py-1.5 rounded-full text-xs font-bold">
                                {{ __('enabled') }} <i class="fas fa-check-circle"></i>
                            </span>
                        </div>
                        
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 mb-6">
                            <p class="text-slate-500 text-sm leading-relaxed">{{ __('otp_description') }}</p>
                        </div>

                        <button class="inline-flex items-center gap-2 px-6 py-3 border-2 border-[#1FA7A2] text-[#1FA7A2] hover:bg-[#1FA7A2] hover:text-white font-bold rounded-full transition-all duration-300">
                            {{ __('manage_security') }} <i class="fas fa-shield-alt"></i>
                        </button>
                    </div>
                </div>

                {{-- 3. Notifications Tab --}}
                <div x-show="activeTab === 'notifications'" x-transition.opacity style="display: none;">
                    <div class="bg-white p-10 rounded-[2rem] shadow-sm border border-slate-200 text-center">
                        <div class="w-20 h-20 bg-[#1FA7A2]/10 rounded-full flex items-center justify-center mx-auto mb-6 text-[#1FA7A2]">
                            <i class="fas fa-bell-slash text-3xl"></i>
                        </div>
                        <h5 class="text-slate-400 font-bold text-lg">{{ __('no_new_notifications') }}</h5>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection