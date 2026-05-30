@extends('layouts.app')

@section('content')

    {{-- الحاوية الرئيسية --}}
    <div class="min-h-screen bg-slate-50 font-['Tajawal'] pt-32 pb-20 relative flex flex-col overflow-hidden" 
         dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

        {{-- خلفية جمالية (Blobs) --}}
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#1FA7A2]/5 blur-[120px] rounded-full mix-blend-multiply"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-teal-500/5 blur-[100px] rounded-full mix-blend-multiply"></div>
            {{-- نمط الشبكة --}}
            <div class="absolute inset-0 opacity-[0.03]" 
                 style="background-image: radial-gradient(#1FA7A2 1px, transparent 1px); background-size: 32px 32px;">
            </div>
        </div>

        {{-- Hero Section --}}
        <div class="container mx-auto px-4 relative z-10 text-center mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-teal-50 border border-teal-100 text-[#1FA7A2] text-xs font-bold mb-4 animate__animated animate__fadeInDown">
                {{ __('Amr 7 Solutions') }}
            </span>
            
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-6 leading-tight animate__animated animate__fadeInUp">
                {{ __('Services Catalog') }} 
                <span class="bg-clip-text text-transparent bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] relative">
                    Amr 7
                    <svg class="absolute w-full h-3 -bottom-1 left-0 text-[#1FA7A2] opacity-20" viewBox="0 0 200 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.00025 6.99997C25.7501 2.49994 132.5 -1.50005 198 4.99996" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                </span>
            </h1>
            
            <p class="text-lg text-slate-500 max-w-2xl mx-auto leading-relaxed animate__animated animate__fadeInUp animate__delay-100ms">
                {{ __('Explore our wide range of government and business services tailored for your success.') }}
            </p>
        </div>

        {{-- شبكة الخدمات --}}
        <div class="container mx-auto px-4 max-w-7xl relative z-10 flex-grow">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                @forelse($services as $service)
                    @php
                        $serviceTitle = trim((string) (($service->{'title_'.app()->getLocale()} ?? null) ?: $service->title_ar ?: $service->title_en ?: $service->slug ?: __('Service')));
                    @endphp
                    <div class="group h-full bg-white border border-slate-100 rounded-[2rem] p-8 text-center relative transition-all duration-300 hover:-translate-y-2 hover:border-[#1FA7A2]/30 hover:shadow-2xl hover:shadow-[#1FA7A2]/10 flex flex-col animate__animated animate__fadeInUp cursor-pointer">
                        
                        {{-- أيقونة الخدمة --}}
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center rounded-2xl bg-teal-50 text-[#1FA7A2] border border-teal-100 transition-all duration-300 group-hover:bg-[#1FA7A2] group-hover:text-white group-hover:scale-110 group-hover:rotate-3 shadow-sm">
                            @if(isset($service->icon) && $service->icon)
                                <img src="{{ asset('storage/' . $service->icon) }}"
                                     class="w-10 h-10 object-contain transition-all duration-300 group-hover:brightness-0 group-hover:invert"
                                     alt="{{ $serviceTitle }}"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ asset('images/platform-placeholder.png') }}';">
                            @else
                                <i class="fas fa-layer-group text-3xl"></i>
                            @endif
                        </div>

                        {{-- العنوان --}}
                        <h5 class="font-bold text-xl text-slate-900 mb-3 group-hover:text-[#1FA7A2] transition-colors">
                            {{ $serviceTitle }}
                        </h5>

                        {{-- الوصف --}}
                        <p class="text-slate-500 text-sm mb-8 flex-grow leading-relaxed line-clamp-3">
                            {{ Str::limit($service->{'excerpt_'.app()->getLocale()} ?? $service->excerpt_ar, 120) }}
                        </p>

                        {{-- الزر --}}
                        <div class="mt-auto relative z-20">
                            <a href="{{ route('services.show', ['service' => $service->slug]) }}" 
                               class="inline-flex items-center justify-center w-full py-3.5 rounded-xl border border-[#1FA7A2]/20 text-[#1FA7A2] font-bold text-sm bg-teal-50/50 transition-all duration-300 hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] group-hover:shadow-lg">
                                {{ __('View Details') }} 
                                <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} ms-2 transition-transform duration-300 group-hover:translate-x-1 rtl:group-hover:-translate-x-1"></i>
                            </a>
                        </div>
                        
                        <a href="{{ route('services.show', ['service' => $service->slug]) }}"
                           wire:navigate
                           class="absolute inset-0 z-10"
                           aria-label="{{ __('View Details') }}: {{ $serviceTitle }}"></a>
                    </div>
                @empty
                    {{-- حالة لا توجد بيانات --}}
                    <div class="col-span-full py-20 text-center">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-slate-50 rounded-full mb-6 text-slate-300 animate__animated animate__pulse animate__infinite">
                            <i class="fas fa-inbox text-4xl"></i>
                        </div>
                        <h3 class="text-slate-800 font-bold text-xl mb-2">{{ __('No services found') }}</h3>
                        <p class="text-slate-500">{{ __('We are currently updating our services catalog. Please check back later.') }}</p>
                    </div>
                @endforelse

            </div>

            {{-- التصفح --}}
            <div class="mt-16 flex justify-center" dir="ltr">
                {{ $services->links() }} 
            </div>
        </div>

    </div>

@endsection
