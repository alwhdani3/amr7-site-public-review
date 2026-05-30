@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    
    <div class="container mx-auto px-4 relative z-10 pt-40 pb-12">
        
        <div class="max-w-5xl mx-auto">
            {{-- Title --}}
            <h1 class="mb-8 text-3xl md:text-4xl lg:text-5xl font-bold text-slate-900 text-center leading-tight">
                {{ $service->{'title_'.app()->getLocale()} ?? $service->title_ar }}
            </h1>
            
            {{-- Content Card --}}
            <div class="relative overflow-hidden bg-white rounded-3xl border border-slate-200 shadow-sm p-8 md:p-12 mb-12">
                
                {{-- Decorative Corner (Gradient Circle) --}}
                <div class="absolute -top-12 -left-12 rtl:-right-12 rtl:left-auto w-32 h-32 rounded-full bg-gradient-to-br from-[#1FA7A2] to-transparent opacity-10 pointer-events-none"></div>

                {{-- Service Body --}}
                {{-- prose class is ideal here if Tailwind Typography is installed, otherwise standard styling applied --}}
                <div class="text-lg text-slate-600 leading-loose space-y-6">
                    {!! $service->{'content_'.app()->getLocale()} ?? $service->content_ar !!}
                </div>
            </div>

            {{-- Action Button --}}
            <div class="text-center">
                <a href="{{ route('services.request', ['service_id' => $service->id]) }}"
                   rel="nofollow"
                   class="group relative inline-flex items-center justify-center px-10 py-4 text-lg font-bold text-white transition-all duration-300 bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] rounded-full shadow-xl hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#1FA7A2]/30 focus:outline-none focus:ring-4 focus:ring-[#1FA7A2]/20">
                    
                    {{-- Pulse Effect Ring (Simulated) --}}
                    <span class="absolute inset-0 rounded-full border-2 border-white/20 animate-ping opacity-75"></span>
                    
                    <span class="relative z-10 flex items-center">
                        {{ __('Request Service Now') }}
                        <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} mx-2 transition-transform duration-300 group-hover:translate-x-1 rtl:group-hover:-translate-x-1"></i>
                    </span>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
