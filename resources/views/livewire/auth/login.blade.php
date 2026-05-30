<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public LoginForm $form;

    public string $loginType = 'mobile';

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    public function setLoginType($type)
    {
        $this->loginType = $type;
        $this->resetErrorBag();
    }
}; ?>

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
@endpush

<div class="min-h-screen w-full flex items-center justify-center px-4 py-10 bg-[#020617] text-white font-['Tajawal'] bg-[radial-gradient(circle_at_10%_20%,rgba(34,199,194,0.10)_0%,transparent_40%),radial-gradient(circle_at_90%_80%,rgba(15,23,42,1)_0%,transparent_40%)]">
    <div class="w-full max-w-5xl rounded-3xl overflow-hidden border border-white/10 bg-white/5 backdrop-blur-2xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.55)]">
        <div class="grid grid-cols-1 md:grid-cols-2">

            <div class="p-8 sm:p-10 lg:p-12">
                <div class="text-center mb-8">
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                        {{ __('auth_welcome_title') }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-300">
                        {{ __('auth_welcome_subtitle') }}
                    </p>
                </div>

                <x-auth-session-status class="mb-6 text-center text-emerald-300" :status="session('status')" />

                <form wire:submit="login" class="space-y-6">

                    <div class="rounded-2xl bg-black/30 border border-white/10 p-1.5 shadow-inner">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" wire:click="setLoginType('mobile')"
                                class="inline-flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-extrabold transition-all duration-300 active:scale-95
                                    {{ $loginType === 'mobile'
                                        ? 'bg-teal-400 text-slate-900 shadow-[0_8px_20px_rgba(34,199,194,0.25)]'
                                        : 'text-slate-400 hover:text-white hover:bg-white/10' }}"
                            >
                                <i class="fas fa-mobile-alt"></i>
                                <span>{{ __('mobile_number') }}</span>
                            </button>

                            <button type="button" wire:click="setLoginType('email')"
                                class="inline-flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-extrabold transition-all duration-300 active:scale-95
                                    {{ $loginType === 'email'
                                        ? 'bg-teal-400 text-slate-900 shadow-[0_8px_20px_rgba(34,199,194,0.25)]'
                                        : 'text-slate-400 hover:text-white hover:bg-white/10' }}"
                            >
                                <i class="fas fa-envelope"></i>
                                <span>{{ __('email_label') }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-extrabold tracking-widest text-slate-300 uppercase">
                            {{ $loginType === 'mobile' ? __('mobile_number') : __('email_label') }}
                        </label>
                        <div class="group relative flex items-center bg-white/5 border border-white/10 rounded-2xl transition-all focus-within:border-teal-400 focus-within:bg-white/10 focus-within:ring-4 focus-within:ring-teal-400/20">
                            <i class="fas {{ $loginType === 'mobile' ? 'fa-mobile-alt' : 'fa-envelope' }} absolute rtl:right-4 ltr:left-4 text-slate-400 group-focus-within:text-teal-400 transition-colors"></i>
                            <input type="{{ $loginType === 'mobile' ? 'tel' : 'email' }}" wire:model="form.email" required autofocus dir="ltr"
                                placeholder="{{ $loginType === 'mobile' ? __('auth_placeholder_mobile') : __('auth_placeholder_email') }}"
                                class="w-full bg-transparent border-none py-3.5 px-12 outline-none text-white font-bold placeholder:text-slate-500 text-sm focus:ring-0"
                            />
                        </div>
                        @error('form.email') <p class="text-xs font-bold text-rose-400 px-2">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2" x-data="{ show: false }">
                        <div class="flex items-center justify-between px-1">
                            <label class="block text-xs font-extrabold tracking-widest text-slate-300 uppercase">
                                {{ __('auth_label_password') }}
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" wire:navigate class="text-xs font-bold text-teal-400 hover:text-teal-300 transition">
                                    {{ __('auth_forgot_password') }}
                                </a>
                            @endif
                        </div>
                        <div class="group relative flex items-center bg-white/5 border border-white/10 rounded-2xl transition-all focus-within:border-teal-400 focus-within:bg-white/10 focus-within:ring-4 focus-within:ring-teal-400/20">
                            <i class="fas fa-lock absolute rtl:right-4 ltr:left-4 text-slate-400 group-focus-within:text-teal-400 transition-colors"></i>
                            <input :type="show ? 'text' : 'password'" wire:model="form.password" required autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full bg-transparent border-none py-3.5 px-12 outline-none text-white font-bold placeholder:text-slate-500 text-sm focus:ring-0"
                            />
                            <button type="button" @click="show = !show" class="absolute rtl:left-4 ltr:right-4 text-slate-400 hover:text-teal-400 focus:outline-none transition-colors">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        @error('form.password') <p class="text-xs font-bold text-rose-400 px-2">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-1">
                        <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" wire:model="form.remember" class="h-5 w-5 rounded border border-white/20 bg-white/10 text-teal-300 focus:ring-2 focus:ring-teal-300/20" />
                            <span class="text-sm font-bold text-slate-200">{{ __('auth_label_remember') }}</span>
                        </label>
                        @error('form.remember') <p class="mt-2 text-xs font-semibold text-rose-300">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full rounded-2xl px-4 py-4 font-black text-slate-900 transition-all duration-300 bg-gradient-to-r from-teal-400 to-emerald-500 hover:from-teal-300 hover:to-emerald-400 focus:outline-none focus:ring-4 focus:ring-teal-400/30 hover:shadow-[0_10px_25px_rgba(34,199,194,0.3)] active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed group/btn"
                    >
                        <span wire:loading.remove wire:target="login" class="flex items-center justify-center gap-2">
                            {{ __('auth_btn_login') }}
                            <i class="fas fa-arrow-left transition-transform group-hover/btn:-translate-x-1"></i>
                        </span>
                        <span wire:loading wire:target="login" class="flex items-center justify-center gap-2">
                            <i class="fas fa-circle-notch fa-spin"></i>
                            {{ __('auth_loading_login') }}
                        </span>
                    </button>
                </form>

                @if (Route::has('register'))
                    <div class="text-center text-sm text-slate-300 mt-8">
                        <span>{{ __('auth_no_account') }}</span>
                        <a href="{{ route('register') }}" wire:navigate class="ms-1 text-teal-300 font-extrabold hover:underline">
                            {{ __('auth_create_account') }}
                        </a>
                    </div>
                @endif
            </div>

            <div class="hidden md:flex flex-col justify-center p-10 lg:p-12 border-s border-white/10 relative overflow-hidden bg-gradient-to-br from-teal-400/10 via-transparent to-slate-900/80">
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#ffffff 2px, transparent 2px); background-size: 30px 30px;"></div>

                <div class="relative z-20 w-full max-w-sm mx-auto rounded-3xl border border-white/10 bg-white/5 backdrop-blur-xl p-10 text-center shadow-2xl transform transition-transform duration-500 hover:scale-[1.02]">
                    <img src="{{ asset('brand/amr7/amr7-logo-lockup-dark.svg') }}" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}" class="mx-auto mb-6 h-16 w-auto opacity-90 drop-shadow-md"/>

                    <h3 class="text-2xl font-black text-white mb-3">
                        {{ __('auth_brand_slogan') }}
                    </h3>

                    <p class="text-sm font-medium text-teal-100/80 leading-relaxed mb-8">
                        {{ __('auth_brand_desc') }}
                    </p>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl bg-black/30 border border-white/10 p-5 shadow-inner"
                             x-data="{
                                 count: 0,
                                 target: 500,
                                 animate() {
                                     let interval = setInterval(() => {
                                         if(this.count < this.target) {
                                             this.count += 20;
                                         } else {
                                             this.count = this.target;
                                             clearInterval(interval);
                                         }
                                     }, 40);
                                 }
                             }"
                             x-init="animate()">
                            <div class="text-teal-400 font-black text-3xl mb-1">+<span x-text="count"></span></div>
                            <div class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                                {{ __('auth_stat_companies') }}
                            </div>
                        </div>

                        <div class="rounded-2xl bg-black/30 border border-white/10 p-5 shadow-inner"
                             x-data="{
                                 count: 0,
                                 target: 100,
                                 animate() {
                                     let interval = setInterval(() => {
                                         if(this.count < this.target) {
                                             this.count += 4;
                                         } else {
                                             this.count = this.target;
                                             clearInterval(interval);
                                         }
                                     }, 50);
                                 }
                             }"
                             x-init="animate()">
                            <div class="text-emerald-400 font-black text-3xl mb-1"><span x-text="count"></span>%</div>
                            <div class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                                {{ __('auth_stat_success') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 rounded-2xl border border-white/10 bg-black/20 p-5 backdrop-blur-sm">
                        <div class="text-xs text-slate-300 font-bold leading-relaxed">
                            {{ __('Company Formation') }} • {{ __('Compliance') }} • {{ __('Contracts') }}
                        </div>
                        <div class="mt-3 text-sm font-black text-teal-300">
                            <i class="fas fa-bolt ms-1"></i> {{ __('Instant Execution') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
