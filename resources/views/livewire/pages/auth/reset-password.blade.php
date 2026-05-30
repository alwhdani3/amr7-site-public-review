<?php

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            Session::flash('status', __($status));
        } else {
            $this->addError('email', __($status));
        }
    }
}; ?>

{{-- الحاوية الرئيسية: تأخذ كامل الشاشة وتقسمها --}}
<div class="flex flex-col lg:flex-row min-h-screen w-full bg-white font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- القسم الأيمن: النموذج --}}
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 py-12 lg:px-16 xl:px-24 bg-white z-20 relative order-2 lg:order-1">
        <div class="w-full max-w-md mx-auto">
            
            {{-- الشعار --}}
            <div class="mb-10 text-center lg:text-start">
                <a href="/" wire:navigate class="inline-block">
                    <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" class="h-16 w-auto mb-6 object-contain" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
                </a>
                <h2 class="text-3xl font-black text-slate-900 mb-2 leading-tight">نسيت كلمة المرور؟</h2>
                <p class="text-slate-500 font-medium text-sm leading-relaxed">
                    لا تقلق، أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.
                </p>
            </div>

            {{-- رسالة الحالة (نجاح) --}}
            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-start gap-3 animate__animated animate__fadeIn">
                    <span class="text-green-600 text-lg mt-0.5">✅</span>
                    <p class="text-green-700 text-sm font-bold">{{ session('status') }}</p>
                </div>
            @endif

            <form wire:submit="sendPasswordResetLink" class="space-y-6">
                
                {{-- حقل البريد الإلكتروني --}}
                <div class="group relative">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">البريد الإلكتروني</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center px-4 text-slate-400 pointer-events-none rtl:right-auto rtl:left-4 ltr:left-auto ltr:right-4">
                            {{-- أيقونة البريد --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="email" wire:model="email" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] block p-4 ps-12 placeholder-slate-400 transition-all font-bold focus:bg-white outline-none shadow-sm" placeholder="email@example.com" required autofocus>
                    </div>
                    @error('email') 
                        <span class="text-red-500 text-xs font-bold mt-2 block animate__animated animate__fadeIn">
                            🚫 {{ $message }}
                        </span> 
                    @enderror
                </div>

                {{-- زر الإرسال --}}
                <button type="submit" class="w-full py-4 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-black text-lg shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/30 hover:-translate-y-1 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-3 relative overflow-hidden group">
                    <span wire:loading.remove class="relative z-10 flex items-center gap-2">
                        إرسال الرابط 
                        <span class="rtl:rotate-180 transition-transform group-hover:-translate-x-1">🚀</span>
                    </span>
                    <span wire:loading class="relative z-10 flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        جاري الإرسال...
                    </span>
                </button>

                {{-- العودة للدخول --}}
                <div class="text-center mt-8">
                    <a href="{{ route('login') }}" class="inline-flex items-center text-slate-500 hover:text-[#1FA7A2] text-sm font-bold transition-colors group" wire:navigate>
                        <span class="rtl:rotate-180 ms-2 group-hover:translate-x-1 transition-transform">➡️</span>
                        العودة لصفحة الدخول
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- القسم الأيسر: بصري (يظهر فقط في الشاشات الكبيرة) --}}
    {{-- نستخدم sticky و h-screen عشان تثبت الصورة لما تسوي سكرول --}}
    <div class="hidden lg:flex w-1/2 min-h-screen bg-gradient-to-br from-[#f0fdfa] to-[#ccfbf1] items-center justify-center relative overflow-hidden order-1 lg:order-2 sticky top-0 h-screen">
        
        {{-- تأثيرات الخلفية --}}
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#1FA7A2]/10 rounded-full blur-3xl -mr-32 -mt-32 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-[#44BDB8]/20 rounded-full blur-3xl -ml-32 -mb-32 animate-pulse" style="animation-duration: 4s;"></div>
        
        {{-- البطاقة الزجاجية --}}
        <div class="relative z-10 text-center p-12 max-w-lg w-full animate__animated animate__fadeInUp">
            <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-10 rounded-[3rem] shadow-2xl hover:shadow-3xl transition-shadow duration-500">
                
                <div class="mb-8 relative inline-block">
                    <div class="absolute inset-0 bg-[#1FA7A2]/20 blur-xl rounded-full"></div>
                    <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" class="h-24 w-auto relative z-10 object-contain drop-shadow-lg" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
                </div>
                
                <h3 class="text-3xl font-black text-slate-900 mb-4 leading-tight">
                    استعادة الوصول
                </h3>
                <p class="text-slate-600 font-medium leading-loose mb-0 text-lg px-4">
                    نسيت كلمة المرور؟ لا بأس، يحدث ذلك للجميع. استعد حسابك في ثوانٍ وتابع إدارة أعمالك بنجاح.
                </p>
                
            </div>
        </div>
    </div>

</div>
