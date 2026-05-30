@extends('layouts.app')

@section('content')
@if(!empty($officialPageSchema))
    <script type="application/ld+json">{!! json_encode($officialPageSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif

@if(!empty($officialFaqSchema))
    <script type="application/ld+json">{!! json_encode($officialFaqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif

<div class="min-h-screen bg-slate-50 font-['Tajawal'] relative overflow-x-hidden selection:bg-[#1FA7A2] selection:text-white" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:40px_40px] opacity-40"></div>
        <div class="absolute top-[-10%] right-[-5%] w-[600px] h-[600px] bg-[#1FA7A2]/10 rounded-full blur-[120px] opacity-80 animate-blob"></div>
        <div class="absolute top-[30%] left-[-10%] w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-[100px] opacity-60 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-10%] right-[20%] w-[700px] h-[700px] bg-teal-400/10 rounded-full blur-[150px] opacity-50 animate-blob animation-delay-4000"></div>
    </div>

    <section class="relative z-10 pt-32 pb-20 lg:pt-40 lg:pb-28 border-b border-slate-200/50 bg-gradient-to-b from-white/60 to-transparent backdrop-blur-sm">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-5xl mx-auto animate__animated animate__fadeInDown">
                <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-sm font-black mb-8 shadow-sm cursor-default">
                    <i class="fas fa-crown"></i> {{ __('hero_misa_badge') }}
                </div>
                
                <h1 class="text-4xl md:text-5xl lg:text-7xl font-black text-slate-900 mb-8 leading-tight tracking-tight">
                    {{ __('hero_misa_title') }}
                </h1>
                
                <p class="text-lg md:text-xl text-slate-600 mb-12 leading-loose font-medium max-w-3xl mx-auto">
                    {{ $seoDescription }}
                </p>
                
                <div class="flex flex-wrap justify-center gap-5">
                    <a href="#form-section" class="group relative px-10 py-4 rounded-full bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold text-lg shadow-[0_10px_20px_rgba(35,109,111,0.25)] hover:shadow-[0_15px_30px_rgba(35,109,111,0.4)] hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 overflow-hidden">
                        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out"></div>
                        <span class="relative z-10">{{ __('btn_invest_now') }}</span>
                        <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} relative z-10 group-hover:-translate-x-1 transition-transform duration-300"></i> 
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 max-w-6xl relative z-10">
        @include('services.partials.official-content', ['officialContent' => $officialContent ?? null])
    </div>

    <section class="py-24 relative z-10">
        <div class="container mx-auto px-4">
            <div class="text-center mb-20">
                <span class="text-amber-600 font-black tracking-widest text-sm uppercase mb-2 block">{{ __('challenges_subtitle') }}</span>
                <h2 class="text-3xl md:text-4xl font-black text-slate-900">{{ __('challenges_title') }}</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 max-w-6xl mx-auto">
                @foreach($painPoints as $pain)
                    <div class="flex gap-6 bg-white/80 backdrop-blur-md rounded-[2rem] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 hover:border-[#1FA7A2]/30 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-14 h-14 rounded-full bg-slate-50 text-slate-400 group-hover:bg-amber-50 group-hover:text-amber-600 flex items-center justify-center text-2xl shrink-0 transition-colors duration-300">
                            <i class="fas {{ $pain['icon'] }}"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 mb-3">{{ $pain['title'] }}</h3>
                            <p class="text-slate-500 leading-relaxed font-medium">{{ $pain['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-24 bg-gradient-to-b from-white to-slate-50 relative z-10 overflow-hidden border-y border-slate-200/50">
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-20">
                <span class="text-[#1FA7A2] font-black tracking-widest text-sm uppercase mb-2 block">{{ __('solutions_subtitle') }}</span>
                <h2 class="text-3xl md:text-4xl font-black text-slate-900">{{ __('solutions_title') }}</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($services as $service)
                    <div class="group bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100 hover:shadow-2xl hover:shadow-[#1FA7A2]/10 hover:border-[#1FA7A2]/30 transition-all duration-500 relative overflow-hidden text-center">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#1FA7A2] to-teal-400 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                        
                        <div class="w-20 h-20 mx-auto rounded-[1.5rem] bg-gradient-to-br from-slate-50 to-slate-100 group-hover:from-[#1FA7A2] group-hover:to-[#167F7B] text-[#1FA7A2] group-hover:text-white flex items-center justify-center text-3xl mb-8 transition-all duration-500 shadow-sm group-hover:shadow-lg group-hover:-translate-y-2">
                            <i class="fas {{ $service['icon'] }}"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 mb-4">{{ $service['title'] }}</h3>
                        <p class="text-slate-500 text-sm leading-loose font-medium">{{ $service['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-24 relative z-10">
        <div class="container mx-auto px-4 max-w-5xl">
            <div class="bg-[#1FA7A2] rounded-[3rem] p-10 md:p-16 relative overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 w-full h-full bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PHBhdGggZD0iTTIwIDIwTDAgMEgyMHYyMEgyMHoiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIgZmlsbC1ydWxlPSJldmVub2RkIi8+PC9zdmc+')] opacity-30"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-teal-400 rounded-full blur-[80px] opacity-40"></div>
                
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-4xl font-black text-white mb-10 text-center">{{ __('requirements_title') }}</h2>
                    <div class="space-y-6">
                        @foreach($requirements as $req)
                            <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition-colors">
                                <div class="w-10 h-10 rounded-full bg-amber-400 text-slate-900 flex items-center justify-center shrink-0 font-black">
                                    <i class="fas fa-check"></i>
                                </div>
                                <p class="text-white font-bold text-lg">{{ $req }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 relative z-10">
        <div class="container mx-auto px-4 max-w-3xl">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-black text-slate-900">{{ __('faq_misa_title') }}</h2>
            </div>
            <div class="space-y-5" x-data="{ active: 0 }">
                @foreach($faqs as $index => $faq)
                    <div class="rounded-2xl transition-all duration-300 border overflow-hidden"
                         x-bind:class="active === {{ $index }} ? 'bg-white border-[#1FA7A2]/30 shadow-lg shadow-[#1FA7A2]/5' : 'bg-white/60 border-slate-200 hover:border-[#1FA7A2]/20'">
                        <button @click="active = active === {{ $index }} ? null : {{ $index }}" class="w-full flex justify-between items-center p-6 text-start focus:outline-none">
                            <span class="font-black text-lg transition-colors duration-300" x-bind:class="active === {{ $index }} ? 'text-[#1FA7A2]' : 'text-slate-800'">
                                {{ $faq['q'] }}
                            </span>
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors duration-300"
                                 x-bind:class="active === {{ $index }} ? 'bg-[#1FA7A2]/10 text-[#1FA7A2]' : 'bg-slate-50 text-slate-400'">
                                <i class="fas fa-chevron-down transition-transform duration-300" :class="active === {{ $index }} ? 'rotate-180' : ''"></i>
                            </div>
                        </button>
                        <div x-show="active === {{ $index }}" x-collapse>
                            <div class="px-6 pb-6 text-slate-600 text-base leading-loose font-medium border-t border-slate-50 mt-2 pt-4">
                                {{ $faq['a'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-24 relative z-10" id="form-section">
        <div class="container mx-auto px-4 max-w-5xl relative">
            <div class="bg-white/90 backdrop-blur-xl rounded-[3rem] shadow-2xl shadow-slate-200/80 border border-white p-8 md:p-16 relative z-10 overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#1FA7A2] to-amber-500"></div>
                
                <div class="text-center mb-12">
                    <div class="w-20 h-20 bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white rounded-3xl flex items-center justify-center text-3xl mx-auto mb-6 shadow-lg shadow-[#1FA7A2]/30 transform rotate-3 hover:rotate-0 transition-transform duration-300">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-4">{{ __('consult_misa_title') }}</h2>
                    <p class="text-lg text-slate-500 font-medium">{{ __('consult_misa_desc') }}</p>
                </div>
                
                {{-- هنا يمكنك استدعاء مكون الفورم الخاص بك. يمكنك استخدام نفس فورم تأسيس الشركات أو إنشاء واحد جديد --}}
                <livewire:public.landing-company-formation-form />
                
            </div>
        </div>
    </section>

</div>
@endsection
