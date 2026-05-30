@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        
        {{-- Header Section --}}
        <div class="relative mb-10 text-start reveal">
            <div class="flex items-center gap-4 mb-2">
                <div class="w-1.5 h-10 bg-[#1FA7A2] rounded-full"></div>
                <flux:heading size="xl" level="1" class="text-gray-900 font-bold text-3xl">
                    {{ __('account_settings_title') }}
                </flux:heading>
            </div>
            <flux:subheading size="lg" class="mb-6 text-gray-500 text-lg">
                {{ __('account_settings_desc') }}
            </flux:subheading>
            <flux:separator variant="subtle" class="bg-gray-200" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            {{-- Sidebar Navigation --}}
            <div class="lg:col-span-1 space-y-4 reveal">
                <div class="sticky top-24 p-3 rounded-2xl bg-white border border-gray-200 shadow-sm">
                    <flux:navlist>
                        <flux:navlist.item href="#profile" icon="heroicon-o-user-circle" current class="text-gray-500 rounded-xl hover:bg-[#f0fdfa] hover:text-[#1FA7A2] transition duration-200 data-[current]:bg-[#f0fdfa] data-[current]:text-[#1FA7A2]">
                            {{ __('nav_profile') }}
                        </flux:navlist.item>
                        <flux:navlist.item href="#security" icon="heroicon-o-shield-check" class="text-gray-500 rounded-xl hover:bg-[#f0fdfa] hover:text-[#1FA7A2] transition duration-200">
                            {{ __('nav_security') }}
                        </flux:navlist.item>
                        <flux:navlist.item href="#integrations" icon="heroicon-o-cpu-chip" class="text-gray-500 rounded-xl hover:bg-[#f0fdfa] hover:text-[#1FA7A2] transition duration-200">
                            {{ __('nav_integrations') }}
                        </flux:navlist.item>
                        <flux:navlist.item href="#notifications" icon="heroicon-o-bell" class="text-gray-500 rounded-xl hover:bg-[#f0fdfa] hover:text-[#1FA7A2] transition duration-200">
                            {{ __('nav_notifications') }}
                        </flux:navlist.item>
                    </flux:navlist>
                </div>
            </div>

            {{-- Main Content --}}
            <div class="lg:col-span-3 space-y-8">

                {{-- Profile Section --}}
                <section id="profile" class="bg-white border border-gray-200 p-8 rounded-3xl shadow-[0_4px_6px_-1px_rgba(0,0,0,0.02)] hover:shadow-md hover:border-gray-300 transition duration-300 reveal">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-[#f0fdfa] flex items-center justify-center text-[#1FA7A2]">
                            <i class="fas fa-user-edit text-xl"></i>
                        </div>
                        <flux:heading size="lg" class="text-gray-900 font-bold">{{ __('basic_info_title') }}</flux:heading>
                    </div>

                    @if (Route::has('profile.update'))
                        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                            @csrf
                            @method('PATCH')
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <flux:input label="{{ __('label_fullname') }}" name="name" value="{{ auth()->user()->name }}" class="bg-gray-50 border-gray-200 text-gray-800 focus:bg-white focus:border-[#1FA7A2] focus:ring-[#1FA7A2]" />
                                <flux:input label="{{ __('label_email') }}" name="email" type="email" value="{{ auth()->user()->email }}" class="bg-gray-50 border-gray-200 text-gray-800 focus:bg-white focus:border-[#1FA7A2] focus:ring-[#1FA7A2]" />
                                <flux:input label="{{ __('label_phone') }}" name="phone" placeholder="05xxxxxxxx" value="{{ auth()->user()->phone ?? '' }}" class="bg-gray-50 border-gray-200 text-gray-800 focus:bg-white focus:border-[#1FA7A2] focus:ring-[#1FA7A2]" />
                                
                                <flux:select label="{{ __('label_language') }}" name="lang" class="bg-gray-50 border-gray-200 text-gray-800 focus:bg-white focus:border-[#1FA7A2] focus:ring-[#1FA7A2]">
                                    <option value="ar">{{ __('lang_arabic') }}</option>
                                    <option value="en">{{ __('lang_english') }}</option>
                                </flux:select>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <flux:button type="submit" class="bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white border-0 rounded-xl font-semibold px-8 py-2 hover:-translate-y-0.5 hover:shadow-lg transition duration-300">
                                    {{ __('save_changes_btn') }}
                                </flux:button>
                            </div>
                        </form>
                    @else
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                            {{ __('Settings page is currently unavailable.') }}
                        </div>
                    @endif
                </section>

                {{-- Security Section --}}
                <section id="security" class="bg-white border border-gray-200 p-8 rounded-3xl shadow-[0_4px_6px_-1px_rgba(0,0,0,0.02)] hover:shadow-md hover:border-gray-300 transition duration-300 reveal">
                    <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#f0fdfa] flex items-center justify-center text-[#1FA7A2]">
                                <i class="fas fa-shield-alt text-xl"></i>
                            </div>
                            <flux:heading size="lg" class="text-gray-900 font-bold">{{ __('security_title') }}</flux:heading>
                        </div>
                        <span class="bg-green-50 text-green-600 px-3 py-1 rounded-full text-xs font-bold">{{ __('status_active') }}</span>
                    </div>

                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-100 mb-6 flex gap-3">
                        <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                        <p class="text-sm text-blue-700 mb-0 leading-relaxed">
                            {{ __('security_note') }}
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <flux:button variant="outline" class="bg-white border-gray-300 text-gray-700 hover:bg-gray-50">
                            {{ __('change_password_btn') }}
                        </flux:button>
                        <flux:button variant="danger" class="bg-red-50 text-red-600 border border-red-100 hover:bg-red-100">
                            {{ __('disable_otp_btn') }}
                        </flux:button>
                    </div>
                </section>

                {{-- Integrations Section --}}
                <section id="integrations" class="bg-white border border-gray-200 p-8 rounded-3xl shadow-[0_4px_6px_-1px_rgba(0,0,0,0.02)] hover:shadow-md hover:border-gray-300 transition duration-300 reveal">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-[#f0fdfa] flex items-center justify-center text-[#1FA7A2]">
                            <i class="fas fa-plug text-xl"></i>
                        </div>
                        <flux:heading size="lg" class="text-gray-900 font-bold">{{ __('integrations_title') }}</flux:heading>
                    </div>
                    <p class="text-gray-500 text-sm mb-6">{{ __('integrations_desc') }}</p>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:border-[#1FA7A2]/30 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center border border-gray-200 shadow-sm">
                                    <span class="text-gray-900 font-bold text-sm">n8n</span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-gray-900 font-bold">{{ __('n8n_alert_title') }}</h6>
                                    <small class="text-gray-500">{{ __('n8n_alert_desc') }}</small>
                                </div>
                            </div>
                            <flux:switch class="text-[#1FA7A2]" />
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
@endsection