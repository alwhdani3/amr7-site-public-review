@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- Hero Section --}}
    <section class="relative pt-20 pb-12 bg-white border-b border-slate-100 overflow-hidden">
        <div class="absolute inset-0 opacity-40 pointer-events-none"
             style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 40px 40px;">
        </div>

        <div class="container mx-auto px-4 relative z-10 text-center">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-4 animate__animated animate__fadeInUp">
                {{ __('packages_title') }}
            </h1>
            <p class="text-lg text-slate-500 max-w-2xl mx-auto animate__animated animate__fadeInUp delay-100">
                {{ __('packages_subtitle') }}
            </p>
        </div>
    </section>

    {{-- Hidden Calculator --}}
    <section class="py-8">
        <div class="container mx-auto px-4 max-w-5xl">
            <div x-data="{ openCalculator: false }" class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                <button
                    type="button"
                    @click="openCalculator = !openCalculator"
                    class="w-full flex items-center justify-between p-6 text-start rtl:text-right hover:bg-slate-50 transition"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-[#1FA7A2]/10 text-[#1FA7A2] flex items-center justify-center">
                            <i class="fas fa-calculator text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900">حاسبة الباقات</h2>
                            <p class="text-sm text-slate-500 font-medium">افتح الحاسبة فقط إذا كنت تريد تقدير السعر حسب عدد الموظفين</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="hidden sm:inline text-sm font-bold text-[#1FA7A2]">
                            <span x-show="!openCalculator">فتح الحاسبة</span>
                            <span x-show="openCalculator">إخفاء الحاسبة</span>
                        </span>
                        <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300" :class="{ 'rotate-180': openCalculator }"></i>
                    </div>
                </button>

                <div x-show="openCalculator" x-collapse class="border-t border-slate-100">
                    <div x-data="priceCalculator()" class="bg-white flex flex-col md:flex-row">
                        {{-- Inputs --}}
                        <div class="md:w-3/5 p-8 md:p-10 bg-white">
                            <div class="flex items-center gap-3 mb-8">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-[#1FA7A2]">
                                    <i class="fas fa-calculator text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-black text-slate-800">حاسبة الاشتراك</h3>
                                    <p class="text-slate-500 text-sm font-medium">احسب التكلفة الإجمالية شاملة الضريبة</p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-black text-slate-700 mb-2">عدد الموظفين في المنشأة</label>
                                    <div class="relative">
                                        <i class="fas fa-users absolute top-1/2 -translate-y-1/2 ltr:left-4 rtl:right-4 text-slate-400"></i>
                                        <input
                                            type="number"
                                            x-model.number="employees"
                                            min="0"
                                            placeholder="أدخل عدد الموظفين..."
                                            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3 px-12 outline-none text-slate-800 font-bold focus:border-[#1FA7A2] focus:bg-white transition-all"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-black text-slate-700 mb-2">اختر نوع الباقة</label>
                                    <div class="relative">
                                        <i class="fas fa-box absolute top-1/2 -translate-y-1/2 ltr:left-4 rtl:right-4 text-slate-400"></i>
                                        <select x-model="packageType" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl py-3 px-12 outline-none text-slate-800 font-bold focus:border-[#1FA7A2] focus:bg-white transition-all appearance-none cursor-pointer">
                                            <option value="">اختر الباقة المناسبة...</option>
                                            <option value="silver">الباقة الفضية</option>
                                            <option value="gold">الباقة الذهبية</option>
                                            <option value="platinum">الباقة البلاتينية</option>
                                        </select>
                                        <i class="fas fa-chevron-down absolute top-1/2 -translate-y-1/2 ltr:right-4 rtl:left-4 text-slate-400 pointer-events-none text-xs"></i>
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-between p-4 rounded-xl border-2 transition-all duration-300"
                                    :class="packageType !== 'silver' && packageType !== '' ? 'bg-teal-50 border-teal-200 opacity-70' : (wageProtection ? 'bg-[#1FA7A2]/5 border-[#1FA7A2]' : 'bg-slate-50 border-slate-200')"
                                >
                                    <div>
                                        <span class="block text-sm font-black text-slate-700">اشتراك حماية الأجور (WPS)</span>
                                        <span class="text-xs font-bold text-slate-500" x-show="packageType === 'silver'">تكلفة إضافية للباقة الفضية</span>
                                        <span class="text-xs font-bold text-teal-600" x-show="packageType === 'gold' || packageType === 'platinum'">مشمول مجاناً وإلزامياً في هذه الباقة</span>
                                    </div>

                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" x-model="wageProtection" class="sr-only peer" :disabled="packageType !== 'silver'">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] rtl:after:right-[2px] rtl:after:left-auto after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1FA7A2]"></div>
                                    </label>
                                </div>

                                <div class="pt-2">
                                    <button @click="resetForm()" type="button" class="text-sm font-bold text-slate-400 hover:text-rose-500 transition-colors flex items-center gap-2">
                                        <i class="fas fa-redo"></i>
                                        إعادة ضبط الحاسبة
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Result --}}
                        <div class="md:w-2/5 bg-[#0A3041] p-8 md:p-10 text-center flex flex-col justify-center relative overflow-hidden">
                            <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#1FA7A2] opacity-30 rounded-full blur-3xl"></div>
                            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-teal-400 opacity-20 rounded-full blur-3xl"></div>

                            <div class="relative z-10">
                                <p class="text-teal-200 font-bold uppercase tracking-widest text-sm mb-4">التكلفة التقديرية</p>

                                <div x-show="employees <= 100">
                                    <div class="flex items-baseline justify-center gap-2 text-white mb-2">
                                        <span class="text-6xl font-black tracking-tighter" x-text="formatNumber(calculatePrice())">0</span>
                                        <span class="text-xl font-bold text-slate-300">ريال</span>
                                    </div>
                                    <p class="text-slate-400 text-sm font-medium mb-8">سنوياً (شامل ضريبة القيمة المضافة 15%)</p>

                                    <button type="button" class="w-full bg-[#1FA7A2] text-white hover:bg-teal-500 py-4 rounded-xl font-black shadow-lg shadow-[#1FA7A2]/30 transition-all duration-300 flex items-center justify-center gap-3 group">
                                        تأكيد وطلب الباقة
                                        <i class="fas fa-arrow-left group-hover:-translate-x-1 rtl:group-hover:translate-x-1 transition-transform"></i>
                                    </button>
                                </div>

                                <div x-show="employees > 100" style="display: none;" class="bg-rose-500/10 border border-rose-500/30 rounded-2xl p-6 backdrop-blur-sm">
                                    <div class="w-12 h-12 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-exclamation-triangle text-xl"></i>
                                    </div>
                                    <p class="text-white font-bold mb-2 text-lg">أكثر من 100 موظف؟</p>
                                    <p class="text-rose-200 text-sm leading-relaxed mb-6">
                                        للتسعير المخصص للمنشآت الكبيرة، تواصل مع فريق المبيعات.
                                    </p>
                                    <a href="tel:920022444" class="inline-flex items-center justify-center gap-2 bg-white text-[#0A3041] w-full py-3 rounded-xl font-black hover:bg-slate-100 transition-colors">
                                        <i class="fas fa-phone-alt"></i>
                                        اتصل بنا: 920022444
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Packages Grid --}}
    <section class="py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($packages as $pkg)
                    @php
                        $rawFeatures = $pkg->getRawOriginal('features');

                        if (is_string($rawFeatures)) {
                            $decoded = json_decode($rawFeatures, true);
                            $rawFeatures = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                        }

                        if (!is_array($rawFeatures)) {
                            $rawFeatures = [];
                        }

                        $features = [];

                        foreach ($rawFeatures as $section => $items) {
                            if (is_string($section) && is_array($items)) {
                                foreach ($items as $item) {
                                    if (is_string($item) && trim($item) !== '') {
                                        $features[] = trim($item);
                                    }
                                }
                                continue;
                            }

                            if (is_string($items) && trim($items) !== '') {
                                $features[] = trim($items);
                            }
                        }

                        $features = collect($features)->unique()->take(6)->values()->all();
                    @endphp

                    <div class="group relative flex flex-col bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-[#1FA7A2]/10 hover:border-[#1FA7A2] animate__animated animate__fadeInUp">
                        @if($pkg->is_featured)
                            <div class="absolute -top-4 ltr:left-8 rtl:right-8 bg-[#1FA7A2] text-white px-4 py-1.5 rounded-full text-xs font-bold shadow-lg shadow-[#1FA7A2]/30 z-20">
                                {{ __('featured') }}
                            </div>
                        @endif

                        <div class="mb-6">
                            <h4 class="text-xl font-bold text-slate-900 mb-2">{{ $pkg->name }}</h4>
                            @if($pkg->description)
                                <p class="text-sm text-slate-500 line-clamp-2 min-h-[2.5rem] leading-relaxed">
                                    {{ $pkg->description }}
                                </p>
                            @endif
                        </div>

                        <div class="flex justify-between items-end mb-8 pb-6 border-b border-slate-100">
                            <div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-black text-[#1FA7A2]">
                                        {{ number_format((float) $pkg->price, 2) }}
                                    </span>
                                    <span class="text-xs text-slate-400 font-bold">{{ __('currency_sar') }}</span>
                                </div>
                            </div>
                            <div class="bg-[#1FA7A2]/10 text-[#1FA7A2] px-3 py-1.5 rounded-full text-xs font-bold flex items-center gap-1.5">
                                <i class="fas fa-headset"></i>
                                {{ (int) $pkg->consultation_limit }} {{ __('consultations_count') }}
                            </div>
                        </div>

                        <div class="flex-grow mb-8">
                            @if(count($features))
                                <ul class="space-y-3">
                                    @foreach($features as $f)
                                        <li class="flex items-start text-sm text-slate-600">
                                            <i class="fas fa-check-circle text-[#1FA7A2] mt-1 me-3 flex-shrink-0"></i>
                                            <span class="leading-relaxed">{{ $f }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="h-full flex items-center justify-center text-slate-400 text-sm italic">
                                    {{ __('no_features_listed') }}
                                </div>
                            @endif
                        </div>

                        <div class="mt-auto relative z-10">
                            <a href="{{ route('packages.show', $pkg) }}"
                               class="group/btn flex w-full items-center justify-center py-3.5 rounded-full text-white font-bold bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#1FA7A2]/20">
                                {{ __('view_package_details') }}
                                <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} mx-2 transition-transform duration-300 group-hover/btn:translate-x-1 rtl:group-hover/btn:-translate-x-1"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center">
                        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-slate-100 text-slate-300 mb-6">
                            <i class="fas fa-box-open fa-3x"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-400">{{ __('no_packages_available') }}</h3>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('priceCalculator', () => ({
        employees: '',
        packageType: '',
        wageProtection: false,
        vat: 0.15,

        calculatePrice() {
            let empCount = parseInt(this.employees) || 0;
            if (empCount < 1 || this.packageType === '') return 0;

            let cost = 0;

            if (this.packageType === 'silver') {
                cost = 7500;
                if (empCount > 4) cost += (empCount - 4) * 500;
                if (this.wageProtection) cost += 3000;
            } else if (this.packageType === 'gold') {
                cost = 13500;
                if (empCount > 9) cost += (empCount - 9) * 750;
                this.wageProtection = true;
            } else if (this.packageType === 'platinum') {
                cost = 20000;
                if (empCount > 9) cost += (empCount - 9) * 1000;
                this.wageProtection = true;
            }

            return cost + (cost * this.vat);
        },

        resetForm() {
            this.employees = '';
            this.packageType = '';
            this.wageProtection = false;
        },

        formatNumber(num) {
            return Number(num || 0).toLocaleString('en-US');
        },

        init() {
            this.$watch('packageType', value => {
                if (value === 'gold' || value === 'platinum') {
                    this.wageProtection = true;
                } else if (value === 'silver') {
                    this.wageProtection = false;
                }
            });
        }
    }));
});
</script>
@endpush