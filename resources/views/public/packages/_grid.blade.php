<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-start" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    @forelse($packages as $package)
        @php
            // معالجة مصفوفة المميزات
            $rawFeatures = $package->features;
            if (is_string($rawFeatures)) {
                $decoded = json_decode($rawFeatures, true);
                $rawFeatures = json_last_error() === JSON_ERROR_NONE ? $decoded : [$rawFeatures];
            }
            $rawFeatures = is_array($rawFeatures) ? $rawFeatures : [];
            $features = collect($rawFeatures)->map(function ($item) {
                if (is_string($item)) return trim($item);
                if (is_array($item)) return trim($item['text'] ?? $item['title'] ?? $item['name'] ?? $item['label'] ?? $item['value'] ?? '');
                return '';
            })->filter(fn ($item) => $item !== '')->values()->all();

            // إعدادات التصميم الديناميكية بناءً على حالة الباقة (مميزة أم عادية)
            $isFeatured = $package->is_featured ?? false;
            
            $cardBg = $isFeatured 
                ? 'bg-gradient-to-b from-[#1b585a] to-[#1FA7A2] text-white shadow-2xl shadow-[#1FA7A2]/40 transform lg:-translate-y-4 lg:scale-105 border-transparent z-20' 
                : 'bg-white text-slate-900 border-slate-200 shadow-xl shadow-slate-200/50 hover:border-[#1FA7A2]/30 hover:-translate-y-2 z-10';
            
            $textColor = $isFeatured ? 'text-white' : 'text-slate-900';
            $descColor = $isFeatured ? 'text-white/80' : 'text-slate-500';
            $priceColor = $isFeatured ? 'text-white' : 'text-[#1FA7A2]';
            $currencyColor = $isFeatured ? 'text-white/70' : 'text-slate-400';
            $iconColor = $isFeatured ? 'text-[#48e5e8]' : 'text-[#1FA7A2]';
            $divider = $isFeatured ? 'border-white/10' : 'border-slate-100';
            
            $consultationBg = $isFeatured 
                ? 'bg-white/10 text-white border border-white/20' 
                : 'bg-[#1FA7A2]/10 text-[#1FA7A2]';
                
            $btnClass = $isFeatured 
                ? 'bg-white text-[#1FA7A2] hover:bg-slate-50 hover:shadow-lg shadow-white/20' 
                : 'bg-slate-900 text-white hover:bg-[#1FA7A2] hover:shadow-lg shadow-[#1FA7A2]/20';
        @endphp

        <div class="flex flex-col h-full relative">
            {{-- بطاقة الباقة --}}
            <div class="group relative flex flex-col h-full rounded-[2.5rem] p-8 border transition-all duration-500 {{ $cardBg }}">
                
                {{-- شريط الباقة المميزة (Featured Ribbon) --}}
                @if($isFeatured)
                    <div class="absolute -top-5 inset-x-0 flex justify-center z-30">
                        <span class="bg-gradient-to-r from-amber-400 to-orange-500 text-white px-6 py-1.5 rounded-full text-sm font-bold shadow-lg shadow-orange-500/30 flex items-center gap-2">
                            <i class="fas fa-star text-xs"></i>
                            {{ __('featured') ?? 'الباقة الأهم' }}
                        </span>
                    </div>
                @endif

                {{-- Header --}}
                <div class="mb-6">
                    <h4 class="text-2xl font-black mb-3 {{ $textColor }}">{{ $package->name }}</h4>
                    @if(!empty($package->description))
                        <p class="text-sm line-clamp-2 min-h-[2.5rem] leading-relaxed {{ $descColor }}">
                            {{ $package->description }}
                        </p>
                    @endif
                </div>

                {{-- Price & Meta --}}
                <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 mb-8 pb-6 border-b {{ $divider }}">
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-black tracking-tight {{ $priceColor }}">
                            {{ number_format((float) $package->price, 0) }}
                        </span>
                        <span class="text-xs font-bold mb-1 {{ $currencyColor }}">
                            {{ __('currency_sar') }}
                        </span>
                    </div>
                    <div class="inline-flex px-3 py-1.5 rounded-xl text-xs font-bold items-center gap-2 w-fit {{ $consultationBg }}">
                        <i class="fas fa-headset {{ $iconColor }}"></i>
                        {{ (int) $package->consultation_limit }} {{ __('consultations_unit') }}
                    </div>
                </div>

                {{-- Features List --}}
                <div class="flex-grow mb-8">
                    @if(count($features) > 0)
                        <ul class="space-y-4">
                            @foreach($features as $feature)
                                <li class="flex items-start gap-3 text-sm">
                                    <div class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center {{ $isFeatured ? 'bg-white/20' : 'bg-[#1FA7A2]/10' }}">
                                        <i class="fas fa-check text-[10px] {{ $iconColor }}"></i>
                                    </div>
                                    <span class="leading-relaxed font-medium {{ $isFeatured ? 'text-white/90' : 'text-slate-700' }}">
                                        {{ $feature }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="h-full flex items-center justify-center text-sm italic opacity-60 {{ $descColor }}">
                            {{ __('no_features_listed') }}
                        </div>
                    @endif
                </div>

                {{-- Action Button --}}
                <div class="mt-auto space-y-2">
                    <a href="{{ route('packages.show', $package->id) }}"
                       class="group/btn relative flex w-full items-center justify-center py-3.5 rounded-2xl font-bold transition-all duration-300 overflow-hidden {{ $btnClass }}">
                        <span class="relative z-10 flex items-center gap-2">
                            {{ __('view_package_details') }}
                            <i class="fas fa-arrow-left transition-transform duration-300 rtl:scale-x-[-1] group-hover/btn:-translate-x-1 rtl:group-hover/btn:translate-x-1"></i>
                        </span>
                    </a>
                    {{-- Phase 8: secondary "request this package" CTA. Routes through
                         /dashboard?section=subscription; Laravel auth middleware redirects
                         guests to login and lands them back here after auth. --}}
                    <a href="{{ route('dashboard') }}?section=subscription"
                       wire:navigate
                       class="group/req relative flex w-full items-center justify-center py-2.5 rounded-2xl text-sm font-bold transition-all duration-300 bg-white text-[#1FA7A2] hover:bg-[#1FA7A2] hover:text-white border border-[#1FA7A2]/20">
                        <span class="relative z-10 flex items-center gap-2">
                            <i class="fas fa-paper-plane text-[11px]"></i>
                            {{ __('Request this package') === 'Request this package' ? 'اطلب هذه الباقة' : __('Request this package') }}
                        </span>
                    </a>
                </div>

            </div>
        </div>
    @empty
        {{-- حالة عدم وجود باقات (Empty State) بتصميم احترافي --}}
        <div class="col-span-full">
            <div class="bg-white p-12 rounded-[2.5rem] text-center border border-dashed border-slate-300 shadow-sm">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-50 text-slate-300 mb-5 shadow-inner">
                    <i class="fas fa-box-open fa-2x"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">{{ __('no_packages_available') }}</h3>
                <p class="text-slate-500">{{ __('check_back_later_packages') ?? 'لا توجد باقات متاحة حالياً، يرجى العودة لاحقاً.' }}</p>
            </div>
        </div>
    @endforelse
</div>