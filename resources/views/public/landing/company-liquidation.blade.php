@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50/50 font-['Tajawal'] relative overflow-x-hidden selection:bg-[#1FA7A2] selection:text-white" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- Background Ambient Blobs --}}
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:32px_32px] opacity-40"></div>
        <div class="absolute top-[-10%] end-[-5%] w-[600px] h-[600px] bg-[#1FA7A2]/10 rounded-full blur-[120px] opacity-80 animate-blob"></div>
        <div class="absolute top-[20%] start-[-10%] w-[500px] h-[500px] bg-emerald-200/20 rounded-full blur-[100px] opacity-70 animate-blob animation-delay-2000"></div>
    </div>

    {{-- Hero Section --}}
    <section class="relative z-10 pt-32 pb-20 lg:pt-40 lg:pb-28 border-b border-slate-200/50 bg-gradient-to-b from-white/60 to-transparent backdrop-blur-[2px]">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-4xl mx-auto animate__animated animate__fadeInDown">
                
                <div class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full bg-white border border-rose-100 text-rose-600 text-sm font-black mb-8 shadow-sm hover:shadow-md transition-all cursor-default">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                    </span>
                    {{ __('secure_guaranteed_closure') }}
                </div>
                
                <h1 class="text-4xl md:text-5xl lg:text-7xl font-black text-slate-900 mb-8 leading-tight tracking-tight">
                    {{ __('hero_liquidation_title') }}
                </h1>
                
                <p class="text-lg md:text-xl text-slate-500 mb-12 leading-relaxed font-medium max-w-3xl mx-auto">
                    {{ $seoDescription }}
                </p>
                
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="#form-section" class="group relative px-8 py-4.5 rounded-2xl bg-[#1FA7A2] hover:bg-[#167F7B] text-white font-black text-lg shadow-[0_8px_20px_rgba(35,109,111,0.25)] hover:shadow-[0_12px_25px_rgba(35,109,111,0.4)] hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 overflow-hidden">
                        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out"></div>
                        <i class="fas fa-file-signature relative z-10 group-hover:rotate-12 transition-transform duration-300" aria-hidden="true"></i> 
                        <span class="relative z-10">{{ __('btn_liquidate_now') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section class="py-24 relative z-10">
        <div class="container mx-auto px-4">
            <div class="text-center mb-20 animate__animated animate__fadeInUp">
                <span class="inline-block px-4 py-1.5 rounded-full bg-[#1FA7A2]/10 text-[#1FA7A2] font-black tracking-widest text-xs uppercase mb-4 border border-[#1FA7A2]/10">{{ __('our_features') }}</span>
                <h2 class="text-3xl md:text-5xl font-black text-slate-900 tracking-tight">{{ __('how_we_help_you_title') }}</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($highlights as $item)
                    <div class="group bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100 hover:shadow-2xl hover:shadow-[#1FA7A2]/15 hover:border-[#1FA7A2]/30 hover:-translate-y-2 transition-all duration-500 relative overflow-hidden animate__animated animate__fadeInUp" style="animation-delay: {{ $loop->index * 100 }}ms;">
                        {{-- الزخرفة الخلفية للكارت --}}
                        <div class="absolute top-0 end-0 w-32 h-32 bg-gradient-to-bl from-[#1FA7A2]/5 to-transparent rounded-bl-full opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                        
                        <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 group-hover:bg-[#1FA7A2] group-hover:border-[#1FA7A2] text-[#1FA7A2] group-hover:text-white flex items-center justify-center text-2xl mb-8 transition-all duration-500 shadow-sm group-hover:scale-110">
                            <i class="fas {{ $item['icon'] }}" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 mb-4 group-hover:text-[#1FA7A2] transition-colors leading-snug">{{ $item['title'] }}</h3>
                        <p class="text-slate-500 text-sm leading-relaxed font-medium m-0">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Work Mechanism / Steps --}}
    <section class="py-24 bg-white relative z-10 overflow-hidden border-y border-slate-100">
        <div class="absolute end-0 top-1/2 -translate-y-1/2 w-1/3 h-full bg-gradient-to-s from-slate-50/50 to-transparent pointer-events-none"></div>
        <div class="container mx-auto px-4 max-w-6xl relative z-10">
            <div class="text-center mb-20 animate__animated animate__fadeInUp">
                <span class="inline-block px-4 py-1.5 rounded-full bg-[#1FA7A2]/10 text-[#1FA7A2] font-black tracking-widest text-xs uppercase mb-4 border border-[#1FA7A2]/10">{{ __('work_mechanism') }}</span>
                <h2 class="text-3xl md:text-5xl font-black text-slate-900 tracking-tight">{{ __('liquidation_steps_title') }}</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 relative">
                {{-- خط وهمي يربط بين الخطوات (يظهر في الشاشات الكبيرة) --}}
                <div class="hidden md:block absolute top-1/2 start-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-[2px] bg-slate-100 -z-10"></div>
                <div class="hidden md:block absolute top-1/2 start-1/2 -translate-x-1/2 -translate-y-1/2 w-[2px] h-full bg-slate-100 -z-10"></div>

                @foreach($steps as $index => $step)
                    <div class="relative flex items-start gap-6 p-8 bg-white rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:shadow-[0_15px_40px_rgba(35,109,111,0.08)] hover:border-[#1FA7A2]/30 transition-all duration-300 group hover:-translate-y-1 bg-white/80 backdrop-blur-sm animate__animated animate__fadeInUp" style="animation-delay: {{ $index * 150 }}ms;">
                        <div class="absolute -top-5 start-6 w-12 h-12 bg-gradient-to-br from-[#1FA7A2] to-emerald-500 text-white font-black text-xl flex items-center justify-center rounded-2xl shadow-lg transform group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                            {{ $step[0] }}
                        </div>
                        <div class="pt-4 w-full">
                            <h4 class="font-black text-xl text-slate-800 mb-3 group-hover:text-[#1FA7A2] transition-colors leading-snug">{{ $step[1] }}</h4>
                            <p class="text-slate-500 text-sm leading-relaxed font-medium m-0">{{ $step[2] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ Section (Corrected Alpine.js Structure) --}}
    <section class="py-24 relative z-10">
        <div class="container mx-auto px-4 max-w-3xl">
            <div class="text-center mb-16 animate__animated animate__fadeInUp">
                <span class="inline-block px-4 py-1.5 rounded-full bg-[#1FA7A2]/10 text-[#1FA7A2] font-black tracking-widest text-xs uppercase mb-4 border border-[#1FA7A2]/10">{{ __('knowledge_base') }}</span>
                <h2 class="text-3xl md:text-5xl font-black text-slate-900 tracking-tight">{{ __('faq_title') }}</h2>
            </div>
            
            {{-- الـ x-data يجب أن يغلف جميع الأسئلة وليس داخل الـ loop --}}
            <div class="space-y-4" x-data="{ active: 0 }">
                @foreach($faqs as $index => $faq)
                    <div class="rounded-2xl transition-all duration-300 border overflow-hidden animate__animated animate__fadeInUp" style="animation-delay: {{ $index * 100 }}ms;"
                         x-bind:class="active === {{ $index }} ? 'bg-white border-[#1FA7A2]/30 shadow-lg shadow-[#1FA7A2]/5 scale-[1.01]' : 'bg-white/60 border-slate-200 hover:border-[#1FA7A2]/20 hover:bg-white'">
                        
                        <button @click="active = active === {{ $index }} ? null : {{ $index }}" class="w-full flex justify-between items-center p-6 text-start focus:outline-none focus:ring-2 focus:ring-[#1FA7A2]/10 rounded-2xl">
                            <span class="font-bold text-base md:text-lg transition-colors duration-300 pe-4" x-bind:class="active === {{ $index }} ? 'text-[#1FA7A2]' : 'text-slate-800'">
                                {{ $faq['q'] }}
                            </span>
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 border"
                                 x-bind:class="active === {{ $index }} ? 'bg-[#1FA7A2]/10 border-[#1FA7A2]/20 text-[#1FA7A2]' : 'bg-slate-50 border-slate-100 text-slate-400'">
                                <i class="fas fa-chevron-down text-sm transition-transform duration-300" :class="active === {{ $index }} ? 'rotate-180' : ''" aria-hidden="true"></i>
                            </div>
                        </button>
                        
                        <div x-show="active === {{ $index }}" x-collapse x-cloak>
                            <div class="px-6 pb-6 text-slate-600 text-sm md:text-base leading-relaxed font-medium border-t border-slate-100 mt-2 pt-5">
                                {{ $faq['a'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Call to Action / Form Section --}}
    <section class="py-24 relative z-10" id="form-section">
        <div class="container mx-auto px-4 max-w-5xl relative">
            <div class="absolute top-1/2 start-1/2 -translate-x-1/2 rtl:translate-x-1/2 -translate-y-1/2 w-full max-w-3xl h-full bg-[#1FA7A2]/10 blur-[120px] rounded-full pointer-events-none z-0"></div>
            
            <div class="bg-white/80 backdrop-blur-2xl rounded-[3rem] shadow-2xl shadow-slate-200/50 border border-white/80 p-6 md:p-12 lg:p-16 relative z-10 overflow-hidden">
                <div class="absolute top-0 start-0 w-full h-2 bg-gradient-to-r from-[#1FA7A2] to-emerald-400"></div>
                
                <div class="text-center mb-12">
                    <div class="w-20 h-20 bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white rounded-[1.5rem] flex items-center justify-center text-3xl mx-auto mb-6 shadow-xl shadow-[#1FA7A2]/20 transform -rotate-3 hover:rotate-0 hover:scale-105 transition-all duration-300 border border-white/20">
                        <i class="fas fa-headset" aria-hidden="true"></i>
                    </div>
                    <h2 class="text-3xl md:text-5xl font-black text-slate-900 mb-4 tracking-tight">{{ __('consult_us_start_liquidation') }}</h2>
                    <p class="text-lg text-slate-500 font-medium">{{ __('fill_data_consultant_whatsapp') }}</p>
                </div>
                
                {{-- استدعاء نموذج التصفية الآمن الذي برمجناه --}}
                <livewire:public.landing-company-liquidation-form />
                
            </div>
        </div>
    </section>

</div>
@endsection