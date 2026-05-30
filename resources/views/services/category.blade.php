@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-slate-50 font-['Tajawal'] pb-20 relative flex flex-col" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

        {{-- Hero Section (الخلفية العلوية) --}}
        <div class="relative w-full h-[400px] flex items-center justify-center text-center text-white overflow-hidden">
            {{-- الصورة الخلفية مع تدرج لوني --}}
            <div class="absolute inset-0 bg-cover bg-center z-0 transition-transform duration-1000 hover:scale-105"
                 style="background-image: url('{{ $platform->hero_image ? asset('storage/' . $platform->hero_image) : asset('brand/amr7/amr7-og-image-1200x630.png') }}');">
            </div>
            <div class="absolute inset-0 bg-gradient-to-br from-[#1FA7A2]/95 via-[#167F7B]/90 to-slate-900/80 z-10"></div>

            {{-- المحتوى النصي --}}
            <div class="relative z-20 container mx-auto px-4 pt-24"> 
                <div class="flex items-center justify-center gap-2 text-teal-100/80 text-xs font-bold mb-4 uppercase tracking-widest animate__animated animate__fadeInDown">
                    <a href="{{ route('services.index') }}" class="hover:text-white transition-colors">{{ __('nav_services') }}</a>
                    <i class="fas fa-chevron-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} text-[10px]"></i>
                    <span>{{ $platform->{'name_'.app()->getLocale()} ?? $platform->name_ar }}</span>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-black mb-6 animate__animated animate__fadeInUp drop-shadow-sm">
                    {{ $platform->{'name_'.app()->getLocale()} ?? $platform->name_ar }}
                </h1>
                
                <p class="text-lg text-white/90 max-w-2xl mx-auto leading-relaxed animate__animated animate__fadeInUp animate__delay-100ms font-light">
                    {{ $platform->{'description_'.app()->getLocale()} ?? $platform->description_ar ?? __('browse_services_desc') }}
                </p>
            </div>
        </div>

        {{-- قائمة الخدمات --}}
        <div class="container mx-auto px-4 max-w-7xl -mt-20 relative z-30">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($services as $service)
                    @php
                        $serviceTitle = trim((string) (($service->{'title_'.app()->getLocale()} ?? null) ?: $service->title_ar ?: $service->title_en ?: $service->slug ?: __('Service')));
                    @endphp
                    <div class="group h-full bg-white border border-slate-100 rounded-[2rem] p-6 text-center relative transition-all duration-300 hover:-translate-y-2 hover:border-[#1FA7A2]/20 hover:shadow-xl hover:shadow-[#1FA7A2]/5 flex flex-col animate__animated animate__fadeInUp cursor-pointer">
                        
                        {{-- الأيقونة --}}
                        <div class="w-[70px] h-[70px] mx-auto mb-5 flex items-center justify-center rounded-2xl shadow-lg shadow-[#1FA7A2]/20 bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-[#1FA7A2]/40">
                            @if($service->icon)
                                <img src="{{ asset('storage/' . $service->icon) }}"
                                     class="w-[35px] h-[35px] object-contain brightness-0 invert"
                                     alt="{{ $serviceTitle }}"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ asset('images/platform-placeholder.png') }}';this.classList.remove('brightness-0','invert');">
                            @else
                                <i class="far fa-file-alt text-2xl"></i>
                            @endif
                        </div>

                        {{-- العنوان --}}
                        <h5 class="font-bold text-lg text-slate-900 mb-3 leading-snug group-hover:text-[#1FA7A2] transition-colors">
                            {{ $serviceTitle }}
                        </h5>

                        {{-- الوصف المختصر --}}
                        <p class="text-slate-500 text-sm mb-6 flex-grow leading-relaxed line-clamp-3">
                            {{ Str::limit($service->{'excerpt_'.app()->getLocale()} ?? $service->excerpt_ar, 80) }}
                        </p>

                        {{-- الزر --}}
                        <div class="mt-auto">
                            <a href="{{ route('services.show', ['service' => $service->slug]) }}" 
                               class="inline-flex items-center justify-center w-full py-3 rounded-xl border border-[#1FA7A2]/20 text-[#1FA7A2] font-bold text-sm bg-teal-50/50 transition-all duration-300 hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] hover:shadow-lg relative z-10">
                                {{ __('view_details') }} 
                                <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} ms-2 transition-transform duration-300 group-hover:translate-x-1 rtl:group-hover:-translate-x-1"></i>
                            </a>
                            {{-- رابط كامل --}}
                            <a href="{{ route('services.show', ['service' => $service->slug]) }}"
                               wire:navigate
                               class="absolute inset-0 z-10"
                               aria-label="{{ __('view_details') }}: {{ $serviceTitle }}"></a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center bg-white rounded-[2rem] border border-slate-100 shadow-sm">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-slate-50 rounded-full mb-4 text-slate-300 animate__animated animate__pulse animate__infinite">
                            <i class="fas fa-inbox text-3xl"></i>
                        </div>
                        <h4 class="text-slate-800 font-bold text-lg mb-1">{{ __('no_services_available') }}</h4>
                        <p class="text-slate-500 text-sm">{{ __('check_back_later') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
@endsection
