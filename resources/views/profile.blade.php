@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- Hero Section --}}
    <section class="relative pt-28 pb-20 overflow-hidden bg-gradient-to-b from-white to-slate-50">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-30 pointer-events-none bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] bg-[length:32px_32px]"></div>
        
        {{-- Ambient Blob --}}
        <div class="absolute top-[-50%] left-1/2 -translate-x-1/2 w-[600px] h-[600px] rounded-full bg-[#44BDB8]/10 blur-[120px] pointer-events-none"></div>

        <div class="container mx-auto px-4 relative z-10 text-center">
            <h1 class="text-3xl md:text-5xl font-black text-slate-900 mb-6 animate__animated animate__fadeInUp">
                {{ __('account_settings_title') }} 
                <span class="bg-clip-text text-transparent bg-gradient-to-br from-[#1FA7A2] to-[#167F7B]">
                    {{ __('account_settings_subtitle') }}
                </span>
            </h1>
            
            <p class="text-lg text-slate-500 max-w-2xl mx-auto leading-relaxed animate__animated animate__fadeInUp animate__delay-100ms">
                {{ __('account_settings_desc') }}
            </p>
        </div>
    </section>

    {{-- Settings Content --}}
    <section class="py-12">
        <div class="container mx-auto px-4 max-w-6xl">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                
                {{-- Sidebar Navigation --}}
                <div class="lg:col-span-1 animate__animated animate__fadeInLeft">
                    <div class="bg-white p-2 rounded-2xl shadow-sm border border-slate-200 sticky top-24 z-10">
                        <nav class="flex flex-col space-y-1">
                            <a href="#info" class="flex items-center px-4 py-3 text-slate-600 font-bold rounded-xl transition-all hover:bg-slate-50 hover:text-[#1FA7A2] group">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-teal-50 group-hover:text-[#1FA7A2] transition-colors me-3 rtl:ml-3">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                {{ __('profile_data') }}
                            </a>
                            <a href="#password" class="flex items-center px-4 py-3 text-slate-600 font-bold rounded-xl transition-all hover:bg-slate-50 hover:text-[#1FA7A2] group">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-teal-50 group-hover:text-[#1FA7A2] transition-colors me-3 rtl:ml-3">
                                    <i class="fas fa-key"></i>
                                </div>
                                {{ __('change_password') }}
                            </a>
                            <a href="#security" class="flex items-center px-4 py-3 text-slate-600 font-bold rounded-xl transition-all hover:bg-slate-50 hover:text-[#1FA7A2] group">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-teal-50 group-hover:text-[#1FA7A2] transition-colors me-3 rtl:ml-3">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                {{ __('security_otp') }}
                            </a>
                            <div class="h-px bg-slate-100 my-1 mx-2"></div>
                            <a href="#delete" class="flex items-center px-4 py-3 text-red-500 font-bold rounded-xl transition-all hover:bg-red-50 group">
                                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-400 group-hover:bg-red-100 group-hover:text-red-600 transition-colors me-3 rtl:ml-3">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                {{ __('delete_account') }}
                            </a>
                        </nav>
                    </div>
                </div>

                {{-- Main Content Area --}}
                <div class="lg:col-span-3 space-y-8">

                    {{-- 1. Profile Info --}}
                    <div id="info" class="bg-white p-6 md:p-8 rounded-[2rem] border border-slate-200 shadow-sm animate__animated animate__fadeInUp">
                        <div class="flex items-center mb-8 pb-4 border-b border-slate-100">
                            <div class="w-12 h-12 rounded-2xl bg-teal-50 flex items-center justify-center text-[#1FA7A2] text-xl me-4 rtl:ml-4 shadow-sm border border-teal-100">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <h4 class="text-xl font-black text-slate-900">{{ __('update_basic_info') }}</h4>
                        </div>
                        
                        {{-- 
                            Using Arbitrary Variants to style inner Livewire components 
                            This removes the need for <style> tags
                        --}}
                        <div class="[&_label]:block [&_label]:font-bold [&_label]:text-sm [&_label]:text-slate-500 [&_label]:mb-2 
                                    [&_input]:w-full [&_input]:bg-slate-50 [&_input]:border [&_input]:border-slate-200 [&_input]:text-slate-800 [&_input]:rounded-xl [&_input]:p-3 [&_input]:outline-none [&_input]:transition-all [&_input]:focus:border-[#1FA7A2] [&_input]:focus:ring-4 [&_input]:focus:ring-[#1FA7A2]/10 [&_input]:focus:bg-white
                                    [&_button[type=submit]]:bg-[#1e293b] [&_button[type=submit]]:text-white [&_button[type=submit]]:px-8 [&_button[type=submit]]:py-3 [&_button[type=submit]]:rounded-full [&_button[type=submit]]:font-bold [&_button[type=submit]]:transition-all [&_button[type=submit]]:hover:bg-[#0f172a] [&_button[type=submit]]:hover:-translate-y-0.5 [&_button[type=submit]]:shadow-lg">
                            @livewire('profile.update-profile-information-form')
                        </div>
                    </div>

                    {{-- 2. Password --}}
                    <div id="password" class="bg-white p-6 md:p-8 rounded-[2rem] border border-slate-200 shadow-sm animate__animated animate__fadeInUp">
                        <div class="flex items-center mb-8 pb-4 border-b border-slate-100">
                            <div class="w-12 h-12 rounded-2xl bg-teal-50 flex items-center justify-center text-[#1FA7A2] text-xl me-4 rtl:ml-4 shadow-sm border border-teal-100">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h4 class="text-xl font-black text-slate-900">{{ __('password_security') }}</h4>
                        </div>
                        
                        <div class="[&_label]:block [&_label]:font-bold [&_label]:text-sm [&_label]:text-slate-500 [&_label]:mb-2 
                                    [&_input]:w-full [&_input]:bg-slate-50 [&_input]:border [&_input]:border-slate-200 [&_input]:text-slate-800 [&_input]:rounded-xl [&_input]:p-3 [&_input]:outline-none [&_input]:transition-all [&_input]:focus:border-[#1FA7A2] [&_input]:focus:ring-4 [&_input]:focus:ring-[#1FA7A2]/10 [&_input]:focus:bg-white
                                    [&_button[type=submit]]:bg-[#1e293b] [&_button[type=submit]]:text-white [&_button[type=submit]]:px-8 [&_button[type=submit]]:py-3 [&_button[type=submit]]:rounded-full [&_button[type=submit]]:font-bold [&_button[type=submit]]:transition-all [&_button[type=submit]]:hover:bg-[#0f172a] [&_button[type=submit]]:hover:-translate-y-0.5 [&_button[type=submit]]:shadow-lg">
                            @livewire('profile.update-password-form')
                        </div>
                    </div>

                    {{-- 3. Security (2FA) --}}
                    <div id="security" class="bg-gradient-to-br from-[#f0fdfa] to-white p-6 md:p-8 rounded-[2rem] border border-[#ccfbf1] shadow-sm animate__animated animate__fadeInUp">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-[#1FA7A2] text-xl me-4 rtl:ml-4 shadow-sm border border-teal-100">
                                    <i class="fas fa-shield-check"></i>
                                </div>
                                <h4 class="text-xl font-black text-slate-900">{{ __('two_factor_auth') }}</h4>
                            </div>
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-green-100 text-green-700 text-xs font-bold border border-green-200 shadow-sm">
                                {{ __('active_status') }} <i class="fas fa-check-circle ms-2 rtl:mr-2"></i>
                            </span>
                        </div>
                        
                        <p class="text-slate-600 text-sm mb-8 leading-loose bg-white/50 p-4 rounded-xl border border-teal-100/50">
                            {{ __('otp_description') }} <strong class="text-[#1FA7A2] font-mono text-base px-2 dir-ltr">050 533 6956</strong>
                        </p>
                        
                        <button class="inline-flex items-center px-6 py-3 border-2 border-[#1FA7A2] text-[#1FA7A2] rounded-full text-sm font-bold hover:bg-[#1FA7A2] hover:text-white transition-all shadow-sm hover:shadow-lg">
                            {{ __('manage_verified_mobile') }} <i class="fas fa-external-link-alt ms-2 rtl:mr-2 text-xs"></i>
                        </button>
                    </div>

                    {{-- 4. Delete Account --}}
                    <div id="delete" class="bg-white p-6 md:p-8 rounded-[2rem] border border-red-100 shadow-sm animate__animated animate__fadeInUp">
                        <div class="flex items-center mb-8 pb-4 border-b border-red-50">
                            <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-red-500 text-xl me-4 rtl:ml-4 shadow-sm border border-red-100">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h4 class="text-xl font-black text-red-600">{{ __('danger_zone') }}</h4>
                        </div>
                        
                        <div class="[&_button[type=submit]]:bg-red-500 [&_button[type=submit]]:text-white [&_button[type=submit]]:px-6 [&_button[type=submit]]:py-3 [&_button[type=submit]]:rounded-full [&_button[type=submit]]:font-bold [&_button[type=submit]]:transition-all [&_button[type=submit]]:hover:bg-red-600 [&_button[type=submit]]:shadow-lg [&_button[type=submit]]:shadow-red-500/30">
                            @livewire('profile.delete-user-form')
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

</div>
@endsection