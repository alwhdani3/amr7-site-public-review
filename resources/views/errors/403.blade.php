@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-16 font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="max-w-lg w-full bg-white rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-8 md:p-10 text-center relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-40 h-40 bg-[#1FA7A2]/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-20 h-20 mx-auto rounded-2xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] mb-6 relative z-10">
            <i class="fas fa-shield-halved text-3xl"></i>
        </div>

        <h1 class="text-2xl md:text-3xl font-black text-slate-900 mb-3 relative z-10">
            ليس لديك صلاحية للوصول
        </h1>

        <p class="text-sm md:text-base text-slate-500 leading-relaxed mb-8 relative z-10">
            @isset($exception)
                {{ $exception->getMessage() ?: 'هذا الإجراء يتطلب صلاحيات إضافية أو منشأة نشطة. تأكد من اختيار المنشأة الصحيحة وحاول مرة أخرى.' }}
            @else
                هذا الإجراء يتطلب صلاحيات إضافية أو منشأة نشطة. تأكد من اختيار المنشأة الصحيحة وحاول مرة أخرى.
            @endisset
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center relative z-10">
            @auth
                <a href="{{ route('company.select') }}"
                   class="px-6 py-3 rounded-xl bg-[#1FA7A2] text-white font-bold shadow-lg shadow-[#1FA7A2]/20 hover:bg-[#167F7B] hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-building"></i>
                    اختر المنشأة
                </a>
            @endauth
            <a href="{{ url('/') }}"
               class="px-6 py-3 rounded-xl bg-slate-50 text-slate-600 font-bold border border-slate-200 hover:bg-slate-100 transition-all flex items-center justify-center gap-2">
                <i class="fas fa-home"></i>
                العودة للرئيسية
            </a>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 text-xs font-bold text-slate-400 relative z-10">
            رمز الخطأ: <span class="font-mono text-slate-500">403</span>
        </div>
    </div>
</div>
@endsection
