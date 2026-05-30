@extends('layouts.app')

@push('styles')
<style>
    /* تأثيرات إضاءة متحركة في الخلفية للفخامة */
    @keyframes slow-blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob { animation: slow-blob 8s infinite; }
    .animation-delay-2000 { animation-delay: 2s; }
    .animation-delay-4000 { animation-delay: 4s; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50/80 font-['Tajawal'] relative overflow-hidden selection:bg-[#1FA7A2] selection:text-white" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- الخلفية الفاخرة (Ambient & Grid) --}}
    <div class="absolute inset-0 opacity-[0.2] pointer-events-none" style="background-image: radial-gradient(#1FA7A2 1px, transparent 1px); background-size: 32px 32px;"></div>
    <div class="absolute top-0 ltr:-left-10 rtl:-right-10 w-[30rem] h-[30rem] bg-[#1FA7A2]/10 rounded-full mix-blend-multiply filter blur-3xl animate-blob pointer-events-none"></div>
    <div class="absolute top-20 ltr:-right-10 rtl:-left-10 w-[30rem] h-[30rem] bg-teal-400/10 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000 pointer-events-none"></div>

    <div class="container mx-auto px-4 pt-24 pb-24 relative z-10 max-w-7xl">
        
        {{-- زر العودة (Glassmorphism Pill) --}}
        <div class="mb-10 animate__animated animate__fadeInDown">
            <a href="{{ route('packages.index') }}" 
               class="group inline-flex items-center gap-3 px-2 py-2 pe-6 rtl:pe-2 rtl:ps-6 bg-white/70 backdrop-blur-md border border-white rounded-full text-slate-600 font-bold shadow-sm hover:shadow-md transition-all duration-300 hover:text-[#1FA7A2] hover:bg-white hover:-translate-x-1 rtl:hover:translate-x-1">
                <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-[#1FA7A2]/10 flex items-center justify-center transition-colors">
                    <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} text-sm"></i>
                </div>
                <span>{{ __('back_to_packages') ?? 'العودة للباقات' }}</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            {{-- العمود الأيمن: تفاصيل الباقة (يأخذ 8 أعمدة) --}}
            <div class="lg:col-span-8 space-y-8 animate__animated animate__fadeInUp">
                
                {{-- بطاقة العنوان والوصف (Header Card) --}}
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-12 border border-white shadow-xl shadow-slate-200/40 relative overflow-hidden">
                    {{-- شريط جانبي صغير للزينة --}}
                    <div class="absolute top-0 bottom-0 ltr:left-0 rtl:right-0 w-2 {{ $package->is_featured ? 'bg-gradient-to-b from-amber-400 to-orange-500' : 'bg-[#1FA7A2]' }}"></div>

                    @if($package->is_featured)
                        <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-orange-50 text-orange-600 border border-orange-100 text-sm font-black mb-6 shadow-sm">
                            <i class="fas fa-star text-xs"></i>
                            {{ __('featured_package') ?? 'باقة مميزة' }}
                        </span>
                    @endif

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-slate-900 mb-6 leading-tight">
                        {{ $package->name }}
                    </h1>

                    @if($package->description)
                        <div class="text-lg text-slate-500 leading-relaxed font-medium max-w-3xl">
                            <p>{{ $package->description }}</p>
                        </div>
                    @endif
                </div>

                {{-- بطاقة المميزات المجمعة (Grouped Features) --}}
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-12 border border-white shadow-xl shadow-slate-200/40">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-5 mb-10 pb-8 border-b border-slate-100/80">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#1FA7A2]/10 to-teal-400/10 flex items-center justify-center text-[#1FA7A2] shadow-inner">
                            <i class="fas fa-gem text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-1">
                                {{ __('package_features') ?? 'ماذا تتضمن هذه الباقة؟' }}
                            </h2>
                            <p class="text-slate-500 font-medium text-sm">نقدم لك حزمة متكاملة مصممة بعناية لتلبية متطلباتك.</p>
                        </div>
                    </div>

                    {{-- منطق الـ PHP الذكي (بدون تغيير) --}}
                    @php
                        $rawFeatures = $package->getRawOriginal('features');
                        if (is_string($rawFeatures)) {
                            $decoded = json_decode($rawFeatures, true);
                            $rawFeatures = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                        }
                        if (!is_array($rawFeatures)) $rawFeatures = [];

                        $groupedFeatures = [];
                        foreach ($rawFeatures as $section => $items) {
                            if (is_string($section) && is_array($items)) {
                                $cleanItems = collect($items)->map(fn ($item) => is_string($item) ? trim($item) : '')->filter(fn ($item) => $item !== '')->values()->all();
                                if (!empty($cleanItems)) {
                                    $groupedFeatures[] = ['title' => trim($section), 'items' => $cleanItems];
                                }
                                continue;
                            }
                            if (is_string($items) && trim($items) !== '') {
                                $groupedFeatures[] = ['title' => null, 'items' => [trim($items)]];
                                continue;
                            }
                            if (is_array($items)) {
                                $title = trim($items['title'] ?? $items['name'] ?? $items['label'] ?? '');
                                $nestedItems = $items['items'] ?? $items['features'] ?? null;
                                if (is_array($nestedItems)) {
                                    $cleanItems = collect($nestedItems)->map(fn ($item) => is_string($item) ? trim($item) : '')->filter(fn ($item) => $item !== '')->values()->all();
                                    if (!empty($cleanItems)) {
                                        $groupedFeatures[] = ['title' => $title !== '' ? $title : null, 'items' => $cleanItems];
                                    }
                                }
                            }
                        }
                    @endphp

                    {{-- عرض المميزات بأسلوب الوحدات (Modules) --}}
                    @if(count($groupedFeatures))
                        <div class="space-y-8">
                            @foreach($groupedFeatures as $group)
                                <div class="relative rounded-[2rem] bg-slate-50/50 border border-slate-100 p-6 md:p-8 hover:bg-white hover:shadow-lg hover:border-[#1FA7A2]/20 transition-all duration-300">
                                    @if(!empty($group['title']))
                                        <h3 class="flex items-center gap-3 text-lg font-black text-slate-800 mb-6">
                                            <span class="w-2 h-2 rounded-full bg-[#1FA7A2]"></span>
                                            {{ $group['title'] }}
                                        </h3>
                                    @endif

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($group['items'] as $item)
                                            <div class="group/item flex items-start gap-4 p-4 rounded-2xl bg-white border border-slate-100 hover:border-[#1FA7A2]/30 transition-all duration-300">
                                                <div class="mt-0.5 flex-shrink-0 w-6 h-6 rounded-full bg-[#1FA7A2]/5 flex items-center justify-center text-[#1FA7A2] group-hover/item:bg-[#1FA7A2] group-hover/item:text-white group-hover/item:scale-110 transition-all">
                                                    <i class="fas fa-check text-xs"></i>
                                                </div>
                                                <span class="text-slate-700 font-medium leading-relaxed">{{ $item }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-16 text-center bg-slate-50/50 rounded-[2rem] border-2 border-dashed border-slate-200">
                            <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center mb-4 shadow-sm">
                                <i class="far fa-folder-open text-3xl text-slate-300"></i>
                            </div>
                            <h4 class="text-lg font-bold text-slate-700 mb-1">لا توجد بيانات</h4>
                            <p class="text-slate-500 font-medium">{{ __('no_features_listed') ?? 'لم يتم إدراج مميزات لهذه الباقة بعد.' }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- العمود الأيسر: شريط الدفع والتسعير (Sticky Checkout Sidebar) --}}
            <div class="lg:col-span-4 sticky top-28 animate__animated animate__fadeInUp animate__delay-1s">
                @php
                    $isFeatured = $package->is_featured;
                    $sidebarBg = $isFeatured ? 'bg-gradient-to-br from-[#1b585a] to-[#1FA7A2] text-white border-transparent shadow-[0_20px_50px_-12px_rgba(35,109,111,0.5)]' : 'bg-white text-slate-900 border-white shadow-xl shadow-slate-200/50';
                    $textColor = $isFeatured ? 'text-white' : 'text-[#1FA7A2]';
                    $mutedText = $isFeatured ? 'text-teal-100' : 'text-slate-500';
                    $divider = $isFeatured ? 'border-white/10' : 'border-slate-100';
                    $boxBg = $isFeatured ? 'bg-white/10 border-white/10 text-white backdrop-blur-sm' : 'bg-slate-50 border-slate-100 text-slate-900';
                    $btnClass = $isFeatured 
                        ? 'bg-white text-[#1FA7A2] hover:bg-teal-50 shadow-lg shadow-white/20' 
                        : 'bg-slate-900 text-white hover:bg-[#1FA7A2] shadow-lg shadow-slate-900/20';
                @endphp

                <div class="rounded-[2.5rem] p-8 md:p-10 border {{ $sidebarBg }} transition-all duration-300 relative overflow-hidden">
                    {{-- توهج خلفي للبطاقة المميزة --}}
                    @if($isFeatured)
                        <div class="absolute -top-24 -right-24 w-48 h-48 bg-white opacity-10 rounded-full blur-2xl"></div>
                    @endif
                    
                    {{-- التسعير --}}
                    <div class="text-center mb-8 pb-8 border-b {{ $divider }} relative z-10">
                        <p class="text-sm font-black uppercase tracking-widest mb-4 {{ $mutedText }}">
                            {{ __('package_price') ?? 'قيمة الاستثمار' }}
                        </p>
                        <div class="flex justify-center items-end gap-2 mb-2">
                            <span class="text-6xl font-black tracking-tighter {{ $textColor }}">
                                {{ number_format((float) $package->price, 0) }}
                            </span>
                            <span class="text-xl font-bold mb-2 {{ $mutedText }}">
                                {{ __('currency_sar') }}
                            </span>
                        </div>
                        <p class="text-sm font-medium {{ $mutedText }}">
                            {{ __('per_subscription') ?? 'تدفع مرة واحدة' }}
                        </p>
                    </div>

                    {{-- الاستشارات --}}
                    <div class="rounded-2xl p-6 mb-8 border flex items-center gap-5 relative z-10 {{ $boxBg }}">
                        <div class="w-14 h-14 rounded-full flex-shrink-0 flex items-center justify-center {{ $isFeatured ? 'bg-white/20' : 'bg-[#1FA7A2]/10 text-[#1FA7A2]' }}">
                            <i class="fas fa-user-tie text-2xl"></i>
                        </div>
                        <div>
                            <div class="text-3xl font-black leading-none mb-1">
                                {{ (int) $package->consultation_limit }}
                            </div>
                            <div class="text-sm font-medium {{ $mutedText }}">
                                {{ __('consultations_included') ?? 'جلسة استشارية متخصصة' }}
                            </div>
                        </div>
                    </div>

                    {{-- زر الاشتراك — Phase 8: يقود إلى ?section=subscription. للضيف Laravel
                         يحفظ intended URL ويعيده إلى نفس المسار بعد الدخول. --}}
                    <a href="{{ route('dashboard') }}?section=subscription"
                       wire:navigate
                       class="group relative w-full overflow-hidden rounded-2xl py-4 font-black text-lg hover:-translate-y-1 transition-all duration-300 relative z-10 flex items-center justify-center {{ $btnClass }}">
                        <span class="relative z-10 flex items-center justify-center gap-3">
                            {{ __('subscribe_now') ?? 'اشترك الآن' }}
                            <i class="fas fa-rocket rtl:scale-x-[-1] transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1"></i>
                        </span>
                    </a>

                    {{-- Phase 8: clear no-online-payment note so the CTA promise matches reality. --}}
                    <p class="mt-3 text-center text-xs font-medium {{ $mutedText }}">
                        <i class="fas fa-info-circle me-1.5 opacity-70"></i>
                        {{ __('package_cta_note') === 'package_cta_note' ? 'سيتم التواصل معك لتأكيد الاشتراك.' : __('package_cta_note') }}
                    </p>

                    {{-- علامات الثقة المتقدمة (Trust Badges) --}}
                    <div class="mt-8 space-y-4 relative z-10">
                        <div class="flex items-center gap-3 text-sm font-medium {{ $mutedText }}">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $isFeatured ? 'bg-white/10' : 'bg-slate-100 text-slate-600' }}">
                                <i class="fas fa-lock text-xs"></i>
                            </div>
                            <span>{{ __('secure_payment') ?? 'بوابة دفع آمنة ومشفرة 100%' }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm font-medium {{ $mutedText }}">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $isFeatured ? 'bg-white/10' : 'bg-slate-100 text-slate-600' }}">
                                <i class="fas fa-bolt text-xs"></i>
                            </div>
                            <span>{{ __('instant_access') ?? 'بدء العمل الفوري بعد الاشتراك' }}</span>
                        </div>
                    </div>
                </div>

                {{-- ماذا بعد الاشتراك؟ (UX Booster) --}}
                <div class="mt-8 bg-white/60 backdrop-blur-sm rounded-[2rem] p-6 border border-white shadow-lg shadow-slate-200/20">
                    <h4 class="text-base font-black text-slate-800 mb-5 flex items-center gap-2">
                        <i class="fas fa-question-circle text-[#1FA7A2]"></i> ماذا بعد الاشتراك؟
                    </h4>
                    <ul class="space-y-4 relative before:absolute before:inset-y-0 ltr:before:left-3 rtl:before:right-3 before:w-0.5 before:bg-slate-200">
                        <li class="relative flex items-start gap-4">
                            <span class="relative z-10 w-6 h-6 rounded-full bg-[#1FA7A2] text-white flex items-center justify-center text-xs font-bold border-4 border-white shrink-0">1</span>
                            <span class="text-sm font-medium text-slate-600 mt-1">إتمام عملية الدفع بنجاح.</span>
                        </li>
                        <li class="relative flex items-start gap-4">
                            <span class="relative z-10 w-6 h-6 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold border-4 border-white shrink-0">2</span>
                            <span class="text-sm font-medium text-slate-600 mt-1">يتواصل معك مستشارك المخصص.</span>
                        </li>
                        <li class="relative flex items-start gap-4">
                            <span class="relative z-10 w-6 h-6 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold border-4 border-white shrink-0">3</span>
                            <span class="text-sm font-medium text-slate-600 mt-1">البدء الفوري في تنفيذ الخدمات.</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection