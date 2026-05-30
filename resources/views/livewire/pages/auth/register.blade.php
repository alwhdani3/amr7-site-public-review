<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

// ✅ هنا التعديل المهم: غيرنا layouts.guest إلى layouts.fullscreen
new #[Layout('layouts.fullscreen')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $mobile = '';
    public string $type = 'business';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $this->mobile = preg_replace('/\D/', '', $this->mobile);
        
        $validated = $this->validate([
            'type' => ['required', 'in:business,individual'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'mobile' => ['required', 'string', 'regex:/^(9665|05)\d{8}$/', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ], [
            'mobile.regex' => __('صيغة الجوال غير صحيحة'),
            'mobile.unique' => __('رقم الجوال مسجل مسبقاً'),
        ]);

        if (str_starts_with($validated['mobile'], '05')) {
            $validated['mobile'] = '966' . substr($validated['mobile'], 1);
        }

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col lg:flex-row min-h-screen w-full bg-white font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- القسم الأيمن: النموذج --}}
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 py-12 lg:px-16 xl:px-24 bg-white z-20 relative order-2 lg:order-1">
        <div class="w-full max-w-md mx-auto">
            
            {{-- الشعار --}}
            <div class="mb-10 text-center lg:text-start">
                <a href="/" wire:navigate class="inline-block">
                    <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" class="h-16 w-auto mb-6 object-contain" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
                </a>
                <h2 class="text-3xl font-black text-slate-900 mb-2 leading-tight">إنشاء حساب جديد</h2>
                <p class="text-slate-500 font-medium text-sm leading-relaxed">ابدأ رحلتك معنا، وسجّل بياناتك بسهولة.</p>
            </div>

            {{-- التنبيهات --}}
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm font-bold animate__animated animate__shakeX">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form wire:submit="register" class="space-y-6">
                
                {{-- نوع الحساب --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">نوع الحساب</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer group relative">
                            <input type="radio" wire:model.live="type" value="business" class="peer sr-only">
                            <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center transition-all duration-300 peer-checked:border-[#1FA7A2] peer-checked:bg-[#1FA7A2] peer-checked:text-white peer-checked:shadow-lg hover:border-[#1FA7A2]/30 hover:bg-white cursor-pointer h-full flex flex-col justify-center items-center gap-2">
                                <span class="text-2xl mb-1 opacity-80 group-hover:opacity-100 transition-opacity">🏢</span>
                                <span class="font-bold text-sm">منشأة / شركة</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group relative">
                            <input type="radio" wire:model.live="type" value="individual" class="peer sr-only">
                            <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center transition-all duration-300 peer-checked:border-[#1FA7A2] peer-checked:bg-[#1FA7A2] peer-checked:text-white peer-checked:shadow-lg hover:border-[#1FA7A2]/30 hover:bg-white cursor-pointer h-full flex flex-col justify-center items-center gap-2">
                                <span class="text-2xl mb-1 opacity-80 group-hover:opacity-100 transition-opacity">👤</span>
                                <span class="font-bold text-sm">أفراد</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- الاسم --}}
                <div class="group relative">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">الاسم الكامل</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center px-4 text-slate-400 pointer-events-none rtl:right-auto rtl:left-4 ltr:left-auto ltr:right-4">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="text" wire:model="name" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] block p-4 ps-12 placeholder-slate-400 transition-all font-bold focus:bg-white outline-none shadow-sm" placeholder="الاسم كما في الهوية" required>
                    </div>
                </div>

                {{-- الجوال --}}
                <div class="group relative">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">رقم الجوال</label>
                    <div class="relative" dir="ltr">
                        <div class="absolute inset-y-0 left-0 flex items-center px-4 text-slate-400 pointer-events-none">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <input type="tel" wire:model="mobile" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] block p-4 pl-12 text-left font-mono font-bold focus:bg-white outline-none shadow-sm" placeholder="9665xxxxxxxx" required>
                    </div>
                </div>

                {{-- البريد --}}
                <div class="group relative">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">البريد الإلكتروني</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center px-4 text-slate-400 pointer-events-none rtl:right-auto rtl:left-4 ltr:left-auto ltr:right-4">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input type="email" wire:model="email" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] block p-4 ps-12 placeholder-slate-400 transition-all font-bold focus:bg-white outline-none shadow-sm" placeholder="email@example.com" required>
                    </div>
                </div>

                {{-- كلمات المرور --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="group">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">كلمة المرور</label>
                        <input type="password" wire:model="password" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] block p-4 placeholder-slate-400 transition-all font-bold focus:bg-white outline-none shadow-sm" placeholder="••••••••" required>
                    </div>
                    <div class="group">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">تأكيد كلمة المرور</label>
                        <input type="password" wire:model="password_confirmation" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] block p-4 placeholder-slate-400 transition-all font-bold focus:bg-white outline-none shadow-sm" placeholder="••••••••" required>
                    </div>
                </div>

                {{-- زر الإرسال --}}
                <button type="submit" class="w-full py-4 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-black text-lg shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/30 hover:-translate-y-1 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-3 relative overflow-hidden group mt-6">
                    <span wire:loading.remove>تسجيل حساب جديد</span>
                    <span wire:loading class="flex items-center gap-2">
                        <i class="fas fa-circle-notch fa-spin"></i> جاري التسجيل...
                    </span>
                </button>

                <div class="text-center mt-8">
                    <p class="text-slate-500 text-sm font-medium">
                        لديك حساب بالفعل؟
                        <a href="{{ route('login') }}" class="text-[#1FA7A2] font-bold hover:text-[#167F7B] hover:underline transition-colors" wire:navigate>تسجيل الدخول</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    {{-- القسم الأيسر: بصري (يظهر فقط في الشاشات الكبيرة) --}}
    {{-- Sticky + h-screen يضمن ثبات الصورة مع السكرول --}}
    <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-[#f0fdfa] to-[#ccfbf1] items-center justify-center relative overflow-hidden sticky top-0 h-screen order-1 lg:order-2">
        
        {{-- خلفية متحركة --}}
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#1FA7A2]/10 rounded-full blur-3xl -mr-32 -mt-32 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-[#44BDB8]/20 rounded-full blur-3xl -ml-32 -mb-32 animate-pulse" style="animation-duration: 4s;"></div>
        
        {{-- البطاقة الزجاجية --}}
        <div class="relative z-10 text-center p-12 max-w-lg w-full animate__animated animate__fadeInUp">
            <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-10 rounded-[3rem] shadow-2xl hover:shadow-3xl transition-shadow duration-500">
                
                <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" class="h-24 mx-auto mb-8 object-contain drop-shadow-sm hover:scale-105 transition-transform duration-300" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
                
                <h3 class="text-3xl font-black text-slate-900 mb-4 leading-tight">
                    بوابتك للسوق السعودي
                </h3>
                <p class="text-slate-600 font-medium leading-loose mb-10 text-lg px-4">
                    أكثر من 15 سنة خبرة في الأنظمة السعودية، نرافقك في رحلة نجاحك خطوة بخطوة.
                </p>
                
                <div class="grid grid-cols-2 gap-5">
                    <div class="bg-white/70 p-5 rounded-3xl shadow-sm border border-white hover:-translate-y-1 transition-transform duration-300 group">
                        <h4 class="text-[#1FA7A2] font-black text-3xl mb-1 group-hover:scale-110 transition-transform">+500</h4>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">شركة مؤسسة</span>
                    </div>
                    <div class="bg-white/70 p-5 rounded-3xl shadow-sm border border-white hover:-translate-y-1 transition-transform duration-300 group">
                        <h4 class="text-yellow-500 font-black text-3xl mb-1 group-hover:scale-110 transition-transform">100%</h4>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">نسبة القبول</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
