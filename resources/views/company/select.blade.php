@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] flex items-center justify-center font-['Tajawal'] py-10 px-4 relative overflow-x-hidden" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    <div class="w-full max-w-3xl relative z-10">

        {{-- Phase 2: زر "العودة إلى لوحة التحكم" يظهر فقط لو فيه منشأة نشطة في الـsession --}}
        @php $hasActiveCompany = (bool) session('active_company_id'); @endphp
        <div class="mb-4 flex justify-center animate__animated animate__fadeIn">
            <a href="{{ $hasActiveCompany ? route('dashboard') : url('/') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-500 hover:text-[#0A2540] bg-white/70 hover:bg-white border border-slate-100 shadow-sm transition-all">
                <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-[10px]"></i>
                <span>{{ __('back_to_dashboard') ?: 'العودة إلى لوحة التحكم' }}</span>
            </a>
        </div>

        {{-- 2. رأس الصفحة — Phase 2: header compact، بدون أيقونة ضخمة --}}
        <div class="text-center mb-6 animate__animated animate__fadeInDown">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-white rounded-2xl shadow-sm border border-[#E8ECEF] mb-3 text-[#1FA7A2]">
                <i class="fas fa-building text-2xl"></i>
            </div>
            <h1 class="text-2xl font-black text-[#0A2540] mb-1 tracking-tight">{{ __('اختر المنشأة للمتابعة') }}</h1>
            <p class="text-[#64748B] font-medium text-sm">{{ __('يرجى تحديد المنشأة التي تود إدارتها الآن') }}</p>
        </div>

        {{-- 3. بطاقة المحتوى --}}
        <div class="bg-white rounded-[1.5rem] shadow-lg shadow-slate-200/50 border border-[#E8ECEF] p-6 sm:p-8 animate__animated animate__fadeInUp">
            
            @if($companies->isEmpty())
                {{-- حالة: لا توجد شركات --}}
                <div class="text-center py-12">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 border-2 border-dashed border-slate-200 text-slate-300 group hover:border-[#1FA7A2] hover:text-[#1FA7A2] transition-colors duration-300">
                        <i class="fas fa-plus text-4xl transform group-hover:rotate-90 transition-transform duration-300"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-xl mb-2">{{ __('لا توجد منشآت مرتبطة') }}</h3>
                    <p class="text-sm text-slate-400 mb-8 max-w-xs mx-auto leading-relaxed">
                        {{ __('لم يتم العثور على أي منشأة مرتبطة بحسابك. قم بإضافة منشأتك الأولى للبدء في استخدام النظام.') }}
                    </p>
                    
                    <div class="[&_.add-company-trigger]:w-full [&_.add-company-trigger]:justify-center [&_.add-company-trigger]:py-3 [&_.add-company-trigger]:text-base">
                        <livewire:company.create-company-modal />
                    </div>
                </div>
            @else
                {{-- Polish: selected state أوضح + زر ديناميكي + loading state --}}
                <form action="{{ route('company.select.store') }}" method="POST"
                      x-data="{ selected: null, submitting: false }"
                      x-on:submit="submitting = true">
                    @csrf

                    <div class="space-y-3 max-h-[400px] overflow-y-auto ps-1 pe-2 mb-6
                                scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-transparent hover:scrollbar-thumb-slate-300">

                        @foreach($companies as $company)
                            @php
                                $completion = (int) ($company->profile_completion_percent ?? 0);
                            @endphp
                            <label class="group relative block cursor-pointer select-none">
                                <input type="radio" name="company_id" value="{{ $company->id }}" class="peer sr-only" required
                                       x-on:change="selected = {{ $company->id }}">

                                <div class="relative flex items-center justify-between p-4 rounded-2xl border-2 border-slate-100 bg-slate-50/70
                                            hover:border-[#1FA7A2]/40 hover:bg-white hover:shadow-sm
                                            peer-checked:border-[#0A2540] peer-checked:bg-[#0A2540]/5 peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-[#0A2540]/10
                                            transition-all duration-200 ease-out">

                                    {{-- شريط جانبي عند الاختيار --}}
                                    <span class="absolute inset-y-0 {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} w-1 rounded-{{ app()->getLocale() == 'ar' ? 's' : 'e' }}-2xl bg-[#0A2540] opacity-0 peer-checked:opacity-100 transition-opacity"></span>

                                    <div class="flex items-center gap-4 overflow-hidden w-full">
                                        <div class="w-14 h-14 shrink-0 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-xl font-bold text-slate-600 shadow-sm
                                                    peer-checked:bg-[#0A2540] peer-checked:text-white peer-checked:border-[#0A2540] transition-colors duration-200">
                                            {{ mb_substr($company->name, 0, 1) }}
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center flex-wrap gap-2 mb-1">
                                                <span class="font-extrabold text-[#0F172A] text-base truncate group-hover:text-[#0A2540] transition-colors">
                                                    {{ $company->name }}
                                                </span>

                                                @if(data_get($company, 'pivot.is_active'))
                                                    <span class="shrink-0 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200/50 flex items-center gap-1">
                                                        <i class="fas fa-check-circle text-[8px]"></i> مفعّلة
                                                    </span>
                                                @else
                                                    <span class="shrink-0 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200 flex items-center gap-1">
                                                        <i class="fas fa-pause-circle text-[8px]"></i> غير مفعّلة
                                                    </span>
                                                @endif

                                                {{-- شارة "مختارة" تظهر فقط عند الاختيار --}}
                                                <span x-show="selected === {{ $company->id }}" x-cloak
                                                      class="shrink-0 px-2.5 py-0.5 rounded-md text-[10px] font-black bg-[#0A2540] text-white border border-[#0A2540] flex items-center gap-1 shadow-sm">
                                                    <i class="fas fa-check-circle text-[9px]"></i>
                                                    مختارة
                                                </span>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400 font-medium">
                                                @if($company->unified_number)
                                                    <span class="flex items-center gap-1.5" title="الرقم الموحد 700">
                                                        <i class="fas fa-hashtag text-slate-300"></i>
                                                        <span class="font-mono tracking-wide">{{ $company->unified_number }}</span>
                                                    </span>
                                                @elseif($company->cr_number)
                                                    <span class="flex items-center gap-1.5" title="رقم السجل التجاري">
                                                        <i class="far fa-id-card text-slate-300"></i>
                                                        <span class="font-mono tracking-wide">{{ $company->cr_number }}</span>
                                                    </span>
                                                @endif
                                                @if($company->city)
                                                    <span class="flex items-center gap-1.5">
                                                        <i class="fas fa-map-marker-alt text-slate-300"></i> {{ $company->city }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="mt-3">
                                                <div class="flex items-center justify-between gap-3 mb-1">
                                                    <span class="text-[10px] font-black text-slate-400">{{ __('profile_completion_title') ?: 'اكتمال الملف' }}</span>
                                                    <span class="text-[10px] font-black text-[#0A2540]">{{ $completion }}%</span>
                                                </div>
                                                <div class="h-1.5 rounded-full bg-white border border-slate-100 overflow-hidden">
                                                    <div class="h-full bg-[#1FA7A2] rounded-full transition-all" style="width: {{ $completion }}%"></div>
                                                </div>
                                            </div>

                                            {{-- نص مساعد يظهر فقط عند الاختيار --}}
                                            <p x-show="selected === {{ $company->id }}" x-cloak class="mt-2 text-[11px] font-bold text-[#0A2540] flex items-center gap-1">
                                                <i class="fas fa-circle-check text-[10px]"></i>
                                                تم اختيار المنشأة — يمكنك المتابعة الآن
                                            </p>
                                        </div>
                                    </div>

                                    {{-- دائرة الاختيار --}}
                                    <div class="absolute top-1/2 {{ app()->getLocale() == 'ar' ? 'left-4' : 'right-4' }} -translate-y-1/2 shrink-0">
                                        <div class="w-7 h-7 rounded-full border-2 border-slate-300 bg-white
                                                    peer-checked:border-[#0A2540] peer-checked:bg-[#0A2540]
                                                    flex items-center justify-center transition-all duration-200 shadow-sm">
                                            <i class="fas fa-check text-white text-[11px] opacity-0 peer-checked:opacity-100 transform scale-50 peer-checked:scale-100 transition-all duration-200"></i>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- زر متابعة - يتفعّل بعد اختيار منشأة --}}
                    <button type="submit"
                            x-bind:disabled="!selected || submitting"
                            x-bind:class="selected ? 'bg-[#0A2540] hover:bg-[#0a2540]/90 shadow-lg shadow-[#0A2540]/20' : 'bg-slate-300 cursor-not-allowed'"
                            class="group w-full text-white font-bold py-3.5 rounded-xl transition-all duration-300 flex items-center justify-center gap-3 disabled:opacity-90 disabled:cursor-not-allowed">
                        <span x-show="!submitting && selected">متابعة إلى لوحة المنشأة</span>
                        <span x-show="!submitting && !selected">اختر منشأة للمتابعة</span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <i class="fas fa-circle-notch fa-spin"></i> جاري فتح لوحة التحكم...
                        </span>
                        <i x-show="!submitting && selected" class="fas fa-arrow-left transition-transform duration-300
                                  {{ app()->getLocale() == 'ar' ? 'group-hover:-translate-x-1' : 'group-hover:translate-x-1 rotate-180' }}"></i>
                    </button>
                </form>

                {{-- إضافة شركة جديدة --}}
                <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                    <p class="text-slate-400 text-xs font-bold mb-4 uppercase tracking-wider">{{ __('هل تريد إضافة منشأة أخرى؟') }}</p>
                    <div class="[&_.add-company-trigger]:w-full [&_.add-company-trigger]:justify-center [&_.add-company-trigger]:bg-white [&_.add-company-trigger]:border [&_.add-company-trigger]:border-slate-200 [&_.add-company-trigger]:text-slate-600 [&_.add-company-trigger]:font-bold [&_.add-company-trigger]:py-3 [&_.add-company-trigger]:rounded-xl [&_.add-company-trigger]:shadow-sm [&_.add-company-trigger]:hover:bg-slate-50 [&_.add-company-trigger]:hover:border-slate-300 [&_.add-company-trigger]:transition-all">
                        <livewire:company.create-company-modal />
                    </div>
                </div>
            @endif
        </div>

        {{-- 4. تسجيل الخروج --}}
        <div class="text-center mt-8">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-slate-400 hover:text-red-600 hover:bg-red-50 text-xs font-bold transition-all duration-300 group">
                    <i class="fas fa-sign-out-alt text-lg transition-transform duration-300 {{ app()->getLocale() == 'ar' ? 'group-hover:translate-x-1' : 'group-hover:-translate-x-1 rotate-180' }}"></i> 
                    <span>{{ __('تسجيل الخروج') }}</span>
                </button>
            </form>
        </div>

    </div>
</div>

{{-- ستايل مخصص للسكرول بار في حال لم يعمل Tailwind Arbitrary Values --}}
<style>
    .scrollbar-thin::-webkit-scrollbar { width: 6px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 20px; }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover { background-color: #cbd5e1; }
</style>
@endsection
