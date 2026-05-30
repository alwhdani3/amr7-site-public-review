@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- Hero Section --}}
    <section class="relative overflow-hidden pt-20 pb-20 bg-gradient-to-b from-white to-slate-100">
        {{-- Background Grid Pattern --}}
        <div class="absolute inset-0 opacity-40 pointer-events-none" 
             style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 40px 40px;">
        </div>

        <div class="container mx-auto px-4 relative z-10 text-center pt-8">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-4 animate__animated animate__fadeInUp">
                {{ __('Services Catalog') }} 
                {{-- Gradient Text --}}
                <span class="bg-clip-text text-transparent bg-gradient-to-br from-[#1FA7A2] to-[#167F7B]">
                    Amr 7
                </span>
            </h1>
            <p class="text-lg md:text-xl text-slate-500 mx-auto max-w-3xl animate__animated animate__fadeInUp delay-100 leading-relaxed">
                {{ __('Explore our wide range of government and business services tailored for your success.') }}
            </p>
        </div>
    </section>

    {{-- Services Grid --}}
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            
            {{-- Grid Layout (بديل Row/Col) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($services as $service)
                    @php
                        $serviceTitle = trim((string) (($service->{'title_'.app()->getLocale()} ?? null) ?: $service->title_ar ?: $service->title_en ?: $service->slug ?: __('Service')));
                    @endphp
                    {{-- Service Card --}}
                    <div class="group relative flex flex-col bg-white rounded-3xl p-6 border border-slate-200 shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:border-[#1FA7A2]/30 overflow-hidden animate__animated animate__fadeInUp cursor-pointer">
                        <a href="{{ route('services.show', ['service' => $service->slug]) }}"
                           wire:navigate
                           class="absolute inset-0 z-10"
                           aria-label="{{ __('View Details') }}: {{ $serviceTitle }}"></a>
                        
                        {{-- Icon Box --}}
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white shadow-md transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                            @if(isset($service->icon) && $service->icon)
                                <img src="{{ asset('storage/' . $service->icon) }}"
                                     class="h-8 w-8 object-contain brightness-0 invert"
                                     alt="{{ $serviceTitle }}"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ asset('images/service-placeholder.png') }}';this.classList.remove('brightness-0','invert');">
                            @else
                                <i class="fas fa-briefcase fa-2x"></i>
                            @endif
                        </div>

                        {{-- Card Content --}}
                        <div class="flex flex-grow flex-col text-center">
                            <h5 class="mb-3 text-lg font-bold text-slate-800">
                                {{ $serviceTitle }}
                            </h5>
                            
                            <p class="mb-6 text-sm text-slate-500 line-clamp-3 leading-loose">
                                {{ Str::limit($service->{'excerpt_'.app()->getLocale()} ?? $service->excerpt_ar, 100) }}
                            </p>
                        </div>

                        {{-- Action Button --}}
                        <div class="mt-auto relative z-20">
                            <a href="{{ route('services.show', ['service' => $service->slug]) }}" 
                               wire:navigate
                               class="group/btn relative flex w-full items-center justify-center rounded-full border-2 border-[#1FA7A2] py-2.5 text-sm font-bold text-[#1FA7A2] transition-all duration-300 hover:bg-[#1FA7A2] hover:text-white hover:shadow-lg hover:shadow-[#1FA7A2]/20">
                                {{ __('View Details') }}
                                <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} mx-2 transition-transform duration-300 group-hover/btn:translate-x-1 rtl:group-hover/btn:-translate-x-1"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    {{-- Empty State --}}
                    <div class="col-span-full py-12 text-center">
                        <div class="mb-4 text-slate-300">
                            <i class="fas fa-box-open fa-4x"></i>
                        </div>
                        <h4 class="text-xl font-bold text-slate-400">{{ __('No services found') }}</h4>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-12 flex justify-center">
                {{ $services->links() }}
            </div>
        </div>
    </section>

</div>
@endsection
