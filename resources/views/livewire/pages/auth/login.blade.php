<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

// ✅ استخدام نفس الـ Layout الخاص بالتسجيل
new #[Layout('layouts.fullscreen')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col lg:flex-row min-h-screen w-full bg-white font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- القسم الأيمن: نموذج الدخول --}}
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 py-12 lg:px-16 xl:px-24 bg-white z-20 relative order-2 lg:order-1">
        <div class="w-full max-w-md mx-auto">
            
            {{-- الشعار --}}
            <div class="mb-10 text-center lg:text-start">
                <a href="/" wire:navigate class="inline-block">
                    <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" class="h-16 w-auto mb-6 object-contain" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
                </a>
                <h2 class="text-3xl font-black text-slate-900 mb-2 leading-tight">{{ __('Welcome Back') }} 👋</h2>
                <p class="text-slate-500 font-medium text-sm leading-relaxed">{{ __('Please login to continue') }}</p>
            </div>

            {{-- رسائل الخطأ --}}
            <x-auth-session-status class="mb-6" :status="session('status')" />

            <form wire:submit="login" class="space-y-6">
                
                {{-- البريد الإلكتروني --}}
                <div class="group relative">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Email') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center px-4 text-slate-400 pointer-events-none rtl:right-auto rtl:left-4 ltr:left-auto ltr:right-4">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username"
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] block p-4 ps-12 placeholder-slate-400 transition-all font-bold focus:bg-white outline-none shadow-sm"
                               placeholder="{{ __('auth_placeholder_email') }}">
                    </div>
                    @error('form.email') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- كلمة المرور --}}
                <div class="group relative" x-data="{ show: false }">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Password') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center px-4 text-slate-400 pointer-events-none rtl:right-auto rtl:left-4 ltr:left-auto ltr:right-4">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input wire:model="form.password" id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] block p-4 ps-12 pe-12 placeholder-slate-400 transition-all font-bold focus:bg-white outline-none shadow-sm"
                               placeholder="••••••••">
                        
                        {{-- زر العين --}}
                        <button type="button" @click="show = !show" 
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none rtl:right-auto rtl:left-0 ltr:left-auto ltr:right-0"
                                tabindex="-1">
                            <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('form.password') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- تذكرني ونسيت كلمة المرور --}}
                <div class="flex items-center justify-between mt-2">
                    <label for="remember" class="inline-flex items-center cursor-pointer group">
                        <div class="relative flex items-center">
                            <input wire:model="form.remember" id="remember" type="checkbox"
                                   class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border-2 border-slate-300 transition-all checked:border-[#1FA7A2] checked:bg-[#1FA7A2] group-hover:border-[#1FA7A2]">
                            <i class="fas fa-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100 text-[10px] pointer-events-none"></i>
                        </div>
                        <span class="ms-2 text-sm font-bold text-slate-500 group-hover:text-slate-700 transition-colors">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm font-bold text-[#1FA7A2] hover:text-[#167F7B] hover:underline transition-all" href="{{ route('password.request') }}" wire:navigate>
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                {{-- زر الدخول --}}
                <button type="submit" 
                        class="w-full py-4 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-black text-lg shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/30 hover:-translate-y-1 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-3 relative overflow-hidden group mt-4" 
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Log in') }}</span>
                    <span wire:loading class="flex items-center gap-2">
                        <i class="fas fa-circle-notch fa-spin"></i> {{ __('Processing...') }}
                    </span>
                </button>

                {{-- Phase C — رابط بديل: الدخول برقم الجوال. الخدمة معطّلة افتراضيًا
                     وتعرض رسالة واضحة عند الضغط؛ لا تكسر تجربة البريد + كلمة المرور. --}}
                @if(Route::has('login.phone'))
                    <div class="text-center mt-6">
                        <a href="{{ route('login.phone') }}"
                           class="inline-flex items-center gap-2 text-sm font-bold text-[#1FA7A2] hover:text-[#167F7B] hover:underline transition-colors">
                            <i class="fas fa-mobile-screen"></i>
                            {{ __('auth_method_phone_label') === 'auth_method_phone_label' ? 'الدخول برقم الجوال' : __('auth_method_phone_label') }}
                        </a>
                    </div>
                @endif

                {{-- رابط التسجيل --}}
                <div class="text-center mt-8">
                    <p class="text-slate-500 text-sm font-medium">
                        {{ __('Don\'t have an account?') }}
                        <a href="{{ route('register') }}" class="text-[#1FA7A2] font-bold hover:text-[#167F7B] hover:underline transition-colors ms-1" wire:navigate>
                            {{ __('Register') }}
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    {{-- Polish: القسم الأيسر — نسخ عربية واضحة، تقليل التدرجات، تنظيف الإحصاءات --}}
    <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-slate-50 to-[#f1f5f9] items-center justify-center relative overflow-hidden sticky top-0 h-screen order-1 lg:order-2">

        {{-- خلفية هادئة بدون نبض مزعج --}}
        <div class="absolute top-0 end-0 w-[500px] h-[500px] bg-[#0A2540]/5 rounded-full blur-3xl -me-32 -mt-32 pointer-events-none"></div>
        <div class="absolute bottom-0 start-0 w-[500px] h-[500px] bg-[#1FA7A2]/10 rounded-full blur-3xl -ms-32 -mb-32 pointer-events-none"></div>

        <div class="relative z-10 p-12 max-w-lg w-full">
            <div class="bg-white border border-slate-200 p-10 rounded-3xl shadow-md text-center">

                <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" class="h-20 mx-auto mb-7 object-contain" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">

                <h3 class="text-2xl md:text-3xl font-black text-[#0A2540] mb-3 leading-tight">
                    {{ __('auth_brand_slogan_v2') ?: 'بوابتك للسوق السعودي' }}
                </h3>
                <p class="text-slate-600 font-medium leading-relaxed mb-8 text-sm">
                    {{ __('auth_brand_desc_v2') ?: 'أكثر من 15 عامًا في تأسيس الشركات، التراخيص، والامتثال.' }}
                </p>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl">
                        <div class="text-[#0A2540] font-black text-2xl leading-none mb-1">+500</div>
                        <div class="text-[11px] font-bold text-slate-500">{{ __('auth_stat_companies') ?: 'شركة مؤسَّسة' }}</div>
                    </div>
                    <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl">
                        <div class="text-[#1FA7A2] font-black text-2xl leading-none mb-1">100%</div>
                        <div class="text-[11px] font-bold text-slate-500">{{ __('auth_stat_followup') ?: 'متابعة الطلبات' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
