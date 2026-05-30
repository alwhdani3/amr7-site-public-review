<div class="min-h-screen bg-slate-50 pt-24 pb-20 font-['Tajawal'] relative overflow-x-hidden selection:bg-[#1FA7A2] selection:text-white" 
     dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-[#1FA7A2]/5 via-transparent to-transparent"></div>
        <div class="absolute bottom-0 left-0 w-full h-1/3 bg-gradient-to-t from-white via-slate-50/50 to-transparent"></div>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-16 reveal opacity-0 translate-y-8 transition-all duration-700 ease-out">
            
            <div class="inline-flex flex-wrap items-center justify-center gap-3 bg-white/80 backdrop-blur-sm border border-slate-200 rounded-full px-6 py-2.5 shadow-sm mb-8 text-sm text-slate-600 ring-1 ring-slate-200/50">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#1FA7A2]"></span>
                    </span>
                    {{ __('fs_system_status') }}: <span class="text-[#1FA7A2] font-bold">{{ __('status_open') }}</span>
                </div>
                <span class="text-slate-300 mx-2 hidden sm:inline">|</span>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    {{ __('fs_fiscal_year') }}: <span class="text-slate-800 font-bold font-mono">{{ date('Y') }}/12/31</span>
                </div>
                <span class="text-slate-300 mx-2 hidden sm:inline">|</span>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    {{ __('fs_deadline') }}: <span class="text-amber-600 font-bold font-mono">{{ date('Y') + 1 }}/06/30</span>
                </div>
            </div>

            <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 mb-6 leading-tight tracking-tight">
                {{ __('fs_hero_title_1') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#1FA7A2] to-[#167F7B]">{{ __('fs_hero_title_highlight') }}</span>
                <br>
                <span class="text-lg md:text-xl font-normal text-slate-500 mt-4 block">{{ __('fs_hero_sub_heading') }}</span>
            </h1>
            
            <p class="text-lg text-slate-600 max-w-3xl mx-auto mb-10 leading-relaxed">
                {!! __('fs_hero_desc') !!}
                <br>
                <span class="text-[#1FA7A2] font-bold text-sm block mt-3 bg-teal-50 inline-block px-4 py-1 rounded-full border border-teal-100">{{ __('fs_hero_target_group') }}</span>
            </p>

            <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 p-6 md:p-8 relative overflow-hidden group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-shadow duration-300">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#1FA7A2] via-teal-400 to-transparent"></div>
                
                <div class="flex justify-between items-center mb-6">
                    <span class="font-bold text-slate-800 text-lg flex items-center gap-2">
                        <i class="fas fa-search-dollar text-[#1FA7A2]"></i>
                        {{ __('fs_track_title') }}
                    </span>
                    <span class="px-3 py-1 rounded-full bg-teal-50 text-[#1FA7A2] text-xs font-bold border border-teal-100 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1FA7A2] animate-pulse"></span> {{ __('Live Tracking') }}
                    </span>
                </div>
                
                <div class="relative flex items-center group/input">
                    <div class="absolute rtl:left-4 ltr:right-4 text-slate-400 pointer-events-none group-focus-within/input:text-[#1FA7A2] transition-colors">
                        <i class="fas fa-barcode text-xl"></i>
                    </div>
                    
                    <input type="text" 
                           class="w-full h-14 bg-slate-50 border border-slate-200 rounded-xl rtl:pl-12 rtl:pr-32 ltr:pr-12 ltr:pl-32 text-slate-800 font-bold placeholder-slate-400 focus:bg-white focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 transition-all outline-none" 
                           placeholder="{{ __('fs_track_placeholder') }}" 
                           wire:model.blur="tracking" 
                           wire:keydown.enter="go">
                    
                    <button class="absolute rtl:right-2 ltr:left-2 top-2 bottom-2 bg-[#1FA7A2] hover:bg-[#167F7B] text-white px-6 rounded-lg font-bold transition-all shadow-md hover:shadow-lg flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                            wire:click="go" 
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('btn_search') }}</span>
                        <span wire:loading class="flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i> {{ __('processing') }}
                        </span>
                    </button>
                </div>

                @error('tracking') 
                    <div class="mt-4 p-3 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm font-bold flex items-center gap-2 animate__animated animate__fadeIn">
                        <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                    </div> 
                @enderror
            </div>

            <div class="mt-10 flex flex-wrap justify-center gap-4">
                @auth
                    <a href="{{ route('financial-statements.create') }}" 
                       wire:navigate 
                       class="px-8 py-4 rounded-full bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold text-lg shadow-lg hover:shadow-[#1FA7A2]/20 hover:-translate-y-1 transition-all duration-300 flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i> {{ __('btn_request_fs') }}
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       wire:navigate
                       class="px-8 py-4 rounded-full bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold text-lg shadow-lg hover:shadow-[#1FA7A2]/20 hover:-translate-y-1 transition-all duration-300 flex items-center gap-2">
                        <i class="fas fa-rocket"></i> {{ __('btn_start_now') }}
                    </a>

                    <a href="{{ route('login') }}"
                       wire:navigate
                       class="px-8 py-4 rounded-full bg-white text-slate-700 border border-slate-200 font-bold text-lg shadow-sm hover:bg-slate-50 hover:text-[#1FA7A2] hover:border-[#1FA7A2] transition-all duration-300 flex items-center gap-2">
                        <i class="fas fa-sign-in-alt"></i> {{ __('btn_login') }}
                    </a>
                @endauth
            </div>
        </div>

        <div class="mb-20 reveal opacity-0 translate-y-8 transition-all duration-700 ease-out delay-100">
            <div class="bg-gradient-to-br from-white to-slate-50 rounded-3xl border border-slate-200 p-8 md:p-12 text-center shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-[#1FA7A2]/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>

                <div class="mb-10 relative z-10">
                    <h3 class="text-2xl md:text-3xl font-extrabold text-slate-800 mb-4 flex justify-center items-center gap-3">
                        <i class="fas fa-shield-alt text-[#1FA7A2]"></i> {{ __('fs_exemption_title') }}
                    </h3>
                    <p class="text-slate-500 max-w-2xl mx-auto text-lg">{{ __('fs_exemption_desc') }}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 relative z-10">
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-lg hover:border-[#1FA7A2]/30 hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 mx-auto bg-teal-50 rounded-2xl flex items-center justify-center text-[#1FA7A2] text-2xl mb-4 group-hover:bg-[#1FA7A2] group-hover:text-white group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="text-2xl font-black text-slate-800 mb-2">{{ __('fs_criteria_revenue_val') }}</div>
                        <div class="text-slate-500 text-sm font-bold">{{ __('fs_criteria_revenue_lbl') }}</div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-lg hover:border-[#1FA7A2]/30 hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 mx-auto bg-teal-50 rounded-2xl flex items-center justify-center text-[#1FA7A2] text-2xl mb-4 group-hover:bg-[#1FA7A2] group-hover:text-white group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="text-2xl font-black text-slate-800 mb-2">{{ __('fs_criteria_assets_val') }}</div>
                        <div class="text-slate-500 text-sm font-bold">{{ __('fs_criteria_assets_lbl') }}</div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-lg hover:border-[#1FA7A2]/30 hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 mx-auto bg-teal-50 rounded-2xl flex items-center justify-center text-[#1FA7A2] text-2xl mb-4 group-hover:bg-[#1FA7A2] group-hover:text-white group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="text-2xl font-black text-slate-800 mb-2">{{ __('fs_criteria_emp_val') }}</div>
                        <div class="text-slate-500 text-sm font-bold">{{ __('fs_criteria_emp_lbl') }}</div>
                    </div>
                </div>

                <div class="inline-block bg-amber-50 border border-amber-200 rounded-xl px-6 py-4 text-amber-800 text-sm font-bold relative z-10">
                    <i class="fas fa-info-circle ml-2"></i> {!! __('fs_exemption_alert') !!}
                </div>
            </div>
        </div>

        <div class="mb-20">
            <div class="text-center mb-10 reveal opacity-0 translate-y-8 transition-all duration-700 ease-out delay-200">
                <h2 class="text-3xl font-extrabold text-slate-900 mb-3">{{ __('fs_faq_title') }}</h2>
                <p class="text-slate-500">{{ __('fs_faq_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 reveal opacity-0 translate-y-8 transition-all duration-700 ease-out delay-300" 
                 x-data="{ activeAccordion: null }">
                
                <div class="space-y-4">
                    @foreach([1, 2] as $id)
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden transition-all duration-300" :class="activeAccordion === {{ $id }} ? 'shadow-md border-[#1FA7A2] ring-1 ring-[#1FA7A2]/20' : ''">
                        <button @click="activeAccordion = activeAccordion === {{ $id }} ? null : {{ $id }}" class="w-full flex items-center justify-between p-5 text-start font-bold text-slate-700 hover:text-[#1FA7A2] transition-colors">
                            <span>{{ __('faq_q'.$id) }}</span>
                            <i class="fas fa-chevron-down transition-transform duration-300 text-sm text-slate-400" :class="activeAccordion === {{ $id }} ? 'rotate-180 text-[#1FA7A2]' : ''"></i>
                        </button>
                        <div x-show="activeAccordion === {{ $id }}" x-collapse>
                            <div class="px-5 pb-5 text-slate-500 text-sm leading-relaxed bg-slate-50/50 border-t border-slate-100 pt-4">
                                {!! __('faq_a'.$id) !!}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="space-y-4">
                    @foreach([3, 4] as $id)
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden transition-all duration-300" :class="activeAccordion === {{ $id }} ? 'shadow-md border-[#1FA7A2] ring-1 ring-[#1FA7A2]/20' : ''">
                        <button @click="activeAccordion = activeAccordion === {{ $id }} ? null : {{ $id }}" class="w-full flex items-center justify-between p-5 text-start font-bold text-slate-700 hover:text-[#1FA7A2] transition-colors">
                            <span>{{ __('faq_q'.$id) }}</span>
                            <i class="fas fa-chevron-down transition-transform duration-300 text-sm text-slate-400" :class="activeAccordion === {{ $id }} ? 'rotate-180 text-[#1FA7A2]' : ''"></i>
                        </button>
                        <div x-show="activeAccordion === {{ $id }}" x-collapse>
                            <div class="px-5 pb-5 text-slate-500 text-sm leading-relaxed bg-slate-50/50 border-t border-slate-100 pt-4">
                                {!! __('faq_a'.$id) !!}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm flex flex-col md:flex-row justify-around items-center gap-8 reveal opacity-0 translate-y-8 transition-all duration-700 ease-out delay-300">
            <div class="text-center group">
                <span class="block text-4xl font-black text-slate-900 mb-1 group-hover:text-[#1FA7A2] transition-colors">100%</span>
                <span class="text-sm font-bold text-slate-500">{{ __('stat_guarantee') }}</span>
            </div>
            
            <div class="hidden md:block w-px h-16 bg-gradient-to-b from-transparent via-slate-200 to-transparent"></div>
            <div class="w-full h-px bg-slate-200 md:hidden"></div>

            <div class="text-center group">
                <span class="block text-4xl font-black text-slate-900 mb-1 group-hover:text-[#1FA7A2] transition-colors">0</span>
                <span class="text-sm font-bold text-slate-500">{{ __('stat_fines') }}</span>
            </div>

            <div class="hidden md:block w-px h-16 bg-gradient-to-b from-transparent via-slate-200 to-transparent"></div>
            <div class="w-full h-px bg-slate-200 md:hidden"></div>

            <div class="text-center group">
                <span class="block text-4xl font-black text-slate-900 mb-1 group-hover:text-[#1FA7A2] transition-colors">24</span>
                <span class="text-sm font-bold text-slate-500">{{ __('stat_turnaround') }}</span>
            </div>
        </div>

    </div>

    @script
    <script>
        function initObserver() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.remove('opacity-0', 'translate-y-8');
                        entry.target.classList.add('opacity-100', 'translate-y-0');
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        }

        initObserver();

        Livewire.on('navigated', () => {
            initObserver();
        });
    </script>
    @endscript
</div>
