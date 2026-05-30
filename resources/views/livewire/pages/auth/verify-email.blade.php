<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="min-h-screen flex items-center justify-center bg-slate-50 font-['Tajawal'] relative overflow-hidden" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- Background Decorative Blobs --}}
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#1FA7A2]/5 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-[#44BDB8]/10 rounded-full blur-3xl -ml-32 -mb-32 pointer-events-none"></div>

    <div class="w-full max-w-lg bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 relative z-10 overflow-hidden m-4 animate__animated animate__fadeInUp">
        
        {{-- Header Section with Logo --}}
        <div class="bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] p-10 text-center relative overflow-hidden">
            {{-- Pattern Overlay --}}
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            
            {{-- Logo Container --}}
            <div class="relative z-10">
                <img src="{{ asset('brand/amr7/amr7-logo-lockup-dark.svg') }}" class="h-20 mx-auto mb-6 object-contain drop-shadow-md" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
                <h2 class="text-white font-black text-3xl mb-2">تأكيد هوية الحساب</h2>
                <p class="text-teal-100 text-sm font-medium tracking-wide">خطوة أخيرة للبدء في إدارة أعمالك</p>
            </div>
        </div>

        {{-- Body Content --}}
        <div class="p-8 md:p-12">
            
            <div class="text-center mb-8">
                <p class="text-slate-600 leading-8 text-sm font-medium">
                    مرحباً بك شريكنا العزيز. لضمان أمان بيانات منشأتك، يرجى تفعيل حسابك من خلال الرابط الذي تم إرساله إلى بريدك الإلكتروني المسجل.
                </p>
            </div>

            {{-- Success Notification --}}
            @if (session('status') == 'verification-link-sent')
                <div class="mb-8 p-4 rounded-2xl bg-green-50 border border-green-100 flex items-start gap-4 animate__animated animate__fadeIn">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-green-600 text-sm"></i>
                    </div>
                    <div class="text-start">
                        <h4 class="text-green-800 font-bold text-sm mb-1">تم الإرسال بنجاح</h4>
                        <p class="text-green-600 text-xs leading-relaxed">تم إرسال رابط تفعيل جديد إلى بريدك الإلكتروني الآن.</p>
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="space-y-5">
                
                {{-- Resend Button --}}
                <button wire:click="sendVerification" 
                        wire:loading.attr="disabled"
                        class="w-full py-4 rounded-2xl bg-[#1FA7A2] text-white font-bold text-sm shadow-lg shadow-[#1FA7A2]/20 hover:shadow-xl hover:shadow-[#1FA7A2]/30 hover:bg-[#167F7B] hover:-translate-y-0.5 transition-all duration-300 flex justify-center items-center gap-3 group relative overflow-hidden">
                    
                    <span wire:loading.remove class="flex items-center gap-2 relative z-10">
                        <i class="fas fa-paper-plane rtl:rotate-180 group-hover:translate-x-1 transition-transform duration-300"></i>
                        إعادة إرسال رابط التفعيل
                    </span>
                    
                    <span wire:loading class="flex items-center gap-2 relative z-10">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        جاري الإرسال...
                    </span>
                </button>

                {{-- Logout Button --}}
                <button wire:click="logout" type="button" class="w-full py-2 text-slate-400 hover:text-[#1FA7A2] text-xs font-bold transition-colors duration-200 flex items-center justify-center gap-2 group">
                    <i class="fas fa-sign-out-alt group-hover:rtl:-translate-x-1 transition-transform"></i> 
                    تسجيل الخروج والعودة لاحقاً
                </button>
            </div>
        </div>
        
        {{-- Footer --}}
        <div class="bg-slate-50 p-5 text-center border-t border-slate-100">
            <p class="text-[10px] text-slate-400 font-bold tracking-wide">
                &copy; {{ date('Y') }} شركة آمر سبعة لحلول الأعمال. جميع الحقوق محفوظة.
            </p>
        </div>

    </div>
</div>
