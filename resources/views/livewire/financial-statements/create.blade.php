<div class="min-h-screen bg-slate-50 py-10 font-['Tajawal'] relative" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-[#1FA7A2]/5 via-transparent to-transparent"></div>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ step: @entangle('currentStep').live ?? 1 }">
        
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 animate__animated animate__fadeInDown">
            <div>
                <h1 class="text-3xl font-black text-slate-900">{{ __('create_request_title') }}</h1>
                <p class="text-slate-500 mt-2 font-bold">{{ __('create_request_subtitle') }}</p>
            </div>
            <a href="{{ route('financial-statements.portal') }}" wire:navigate class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-white hover:text-[#1FA7A2] hover:shadow-sm transition-all duration-300">
                <i class="fas fa-arrow-right rtl:rotate-180 text-sm"></i> {{ __('btn_back') }}
            </a>
        </div>

        <div class="mb-8 animate__animated animate__fadeIn">
            <div class="flex items-center justify-between relative">
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-200 rounded-full z-0"></div>
                <div class="absolute rtl:right-0 ltr:left-0 top-1/2 -translate-y-1/2 h-1 bg-[#1FA7A2] rounded-full z-0 transition-all duration-500" :style="'width: ' + ((step - 1) / 2 * 100) + '%'"></div>

                <div class="relative z-10 flex flex-col items-center gap-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-sm transition-all duration-500 shadow-sm"
                         :class="step >= 1 ? 'bg-[#1FA7A2] text-white ring-4 ring-[#1FA7A2]/20' : 'bg-white text-slate-400 border border-slate-200'">
                        <i class="fas fa-building" x-show="step === 1"></i>
                        <i class="fas fa-check" x-show="step > 1" x-cloak></i>
                    </div>
                    <span class="text-xs font-bold" :class="step >= 1 ? 'text-[#1FA7A2]' : 'text-slate-400'">{{ __('step_entity_info') }}</span>
                </div>

                <div class="relative z-10 flex flex-col items-center gap-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-sm transition-all duration-500 shadow-sm"
                         :class="step >= 2 ? 'bg-[#1FA7A2] text-white ring-4 ring-[#1FA7A2]/20' : 'bg-white text-slate-400 border border-slate-200'">
                        <i class="fas fa-cloud-upload-alt" x-show="step === 2"></i>
                        <span x-show="step < 2">2</span>
                        <i class="fas fa-check" x-show="step > 2" x-cloak></i>
                    </div>
                    <span class="text-xs font-bold" :class="step >= 2 ? 'text-[#1FA7A2]' : 'text-slate-400'">{{ __('step_attachments') }}</span>
                </div>

                <div class="relative z-10 flex flex-col items-center gap-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-sm transition-all duration-500 shadow-sm"
                         :class="step >= 3 ? 'bg-[#1FA7A2] text-white ring-4 ring-[#1FA7A2]/20' : 'bg-white text-slate-400 border border-slate-200'">
                        <i class="fas fa-flag-checkered" x-show="step === 3"></i>
                        <span x-show="step < 3">3</span>
                    </div>
                    <span class="text-xs font-bold" :class="step >= 3 ? 'text-[#1FA7A2]' : 'text-slate-400'">{{ __('step_review') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden relative min-h-[400px]">
            
            <div wire:loading.flex wire:target="nextStep, previousStep, submit" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-slate-200 border-t-[#1FA7A2] rounded-full animate-spin"></div>
                    <span class="text-sm font-bold text-[#1FA7A2] animate-pulse">{{ __('processing') }}</span>
                </div>
            </div>

            <form wire:submit="submit">
                
                <div x-show="step === 1" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('label_entity_name') }} <span class="text-rose-500">*</span></label>
                            <div class="relative group">
                                <input type="text" wire:model.blur="entity" class="w-full h-14 bg-slate-50 border-2 border-slate-100 focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-2xl rtl:pr-12 ltr:pl-12 text-slate-800 font-bold outline-none transition-all placeholder-slate-300" placeholder="{{ __('placeholder_company_name') }}">
                                <i class="fas fa-building absolute top-1/2 -translate-y-1/2 rtl:right-5 ltr:left-5 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors"></i>
                            </div>
                            @error('entity') <span class="text-rose-500 text-xs font-bold mt-1.5 block px-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('label_cr_number') }} <span class="text-rose-500">*</span></label>
                            <div class="relative group">
                                <input type="text" wire:model.blur="cr" class="w-full h-14 bg-slate-50 border-2 border-slate-100 focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-2xl rtl:pr-12 ltr:pl-12 text-slate-800 font-mono font-bold outline-none transition-all placeholder-slate-300" placeholder="1010xxxxxxxx" dir="ltr">
                                <i class="fas fa-id-card absolute top-1/2 -translate-y-1/2 rtl:right-5 ltr:left-5 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors"></i>
                            </div>
                            @error('cr') <span class="text-rose-500 text-xs font-bold mt-1.5 block px-1">{{ $message }}</span> @enderror
                        </div>

                        <div x-data="{ customYearOpen: false }">
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('label_fiscal_year') }} <span class="text-rose-500">*</span></label>
                            {{-- Dynamic fiscal year chips: previous 2 years, current, next year, custom. --}}
                            @php
                                $currentYear = (int) date('Y');
                                $fiscalYearChips = [
                                    (string) ($currentYear - 2),
                                    (string) ($currentYear - 1),
                                    (string) $currentYear,
                                    (string) ($currentYear + 1),
                                ];
                            @endphp
                            <div class="flex flex-wrap gap-2">
                                @foreach($fiscalYearChips as $y)
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="year-chip" value="{{ $y }}" wire:model.live="year" class="peer sr-only" @change="customYearOpen = false">
                                        <span class="inline-flex items-center justify-center min-w-[96px] h-12 px-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-slate-700 font-mono font-black text-base transition-all
                                                     hover:border-[#1FA7A2]/30 hover:bg-white
                                                     peer-checked:border-[#1FA7A2] peer-checked:bg-[#1FA7A2]/5 peer-checked:text-[#1FA7A2] peer-checked:ring-2 peer-checked:ring-[#1FA7A2]/20 peer-checked:shadow-sm">
                                            <i class="far fa-calendar-alt me-2 text-slate-400 group-hover:text-[#1FA7A2]"></i>
                                            {{ $y }}
                                        </span>
                                    </label>
                                @endforeach
                                {{-- Custom year toggle --}}
                                <button type="button" @click="customYearOpen = !customYearOpen" class="inline-flex items-center justify-center min-w-[120px] h-12 px-4 rounded-2xl border-2 border-dashed border-slate-200 bg-white text-slate-600 font-bold text-sm transition-all hover:border-[#1FA7A2]/40 hover:text-[#1FA7A2]" :class="customYearOpen ? 'border-[#1FA7A2] text-[#1FA7A2] bg-[#1FA7A2]/5' : ''">
                                    <i class="fas fa-pen me-2 text-xs"></i>
                                    سنة مخصصة
                                </button>
                            </div>
                            {{-- Custom year input — appears only when user explicitly opens it. --}}
                            <div x-show="customYearOpen" x-cloak x-transition class="mt-3 max-w-xs">
                                <input type="number"
                                       wire:model.live="year"
                                       min="1990"
                                       max="{{ $currentYear + 5 }}"
                                       placeholder="مثال: {{ $currentYear - 3 }}"
                                       class="w-full bg-slate-50 border-2 border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-2xl px-4 py-3 text-slate-800 font-mono font-bold outline-none transition-all">
                            </div>
                            @error('year') <span class="text-rose-500 text-xs font-bold mt-1.5 block px-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

             <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" class="p-8">
    <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
        <div class="w-16 h-16 mx-auto rounded-full bg-white shadow-sm flex items-center justify-center mb-4 text-[#1FA7A2]">
            <i class="fas fa-folder-open text-2xl"></i>
        </div>

        <h3 class="text-lg font-black text-slate-800 mb-2">رفع الملفات بعد إنشاء الطلب</h3>
        <p class="text-sm text-slate-500 leading-7 max-w-2xl mx-auto">
            بعد إنشاء الطلب سيتم تحويلك مباشرة إلى صفحة الطلب، وهناك تستطيع رفع المستندات المطلوبة والفواتير بشكل آمن ومنظم.
        </p>
    </div>
</div>
                        
                   
                <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" class="p-8">
                    
                    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 space-y-4">
                        <div class="flex justify-between items-center pb-4 border-b border-slate-200/60">
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">{{ __('label_entity_name') }}</span>
                            <span class="text-sm font-bold text-slate-900">{{ $entity ?? '---' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-4 border-b border-slate-200/60">
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">{{ __('label_cr_number') }}</span>
                            <span class="text-sm font-mono font-bold text-slate-900">{{ $cr ?? '---' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-4 border-b border-slate-200/60">
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">{{ __('label_fiscal_year') }}</span>
                            <span class="text-sm font-mono font-bold text-slate-900">{{ $year ?? '---' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">{{ __('attachments_count') }}</span>
                            <span class="text-sm font-bold text-slate-900 bg-white px-3 py-1 rounded-lg border border-slate-200 shadow-sm">سيتم رفع الملفات بعد إنشاء الطلب</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('label_notes_opt') }}</label>
                        <textarea wire:model.blur="notes" rows="3" class="w-full bg-white border border-slate-200 focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/10 rounded-2xl p-4 text-slate-800 font-bold outline-none transition-all placeholder-slate-300 resize-none custom-scrollbar" placeholder="{{ __('placeholder_notes') }}"></textarea>
                    </div>
                </div>

                {{-- Sticky footer so buttons stay visible even on long forms / small viewports. --}}
                <div class="px-8 py-5 bg-white/95 backdrop-blur-sm border-t border-slate-100 flex items-center justify-between sticky bottom-0 z-20 shadow-[0_-8px_24px_-12px_rgba(15,23,42,0.08)]">
                    <div>
                        <button type="button" x-show="step > 1" wire:click="previousStep" class="px-6 py-3 rounded-xl font-bold text-slate-600 hover:bg-slate-100 transition-colors flex items-center gap-2">
                            <i class="fas fa-arrow-right rtl:rotate-180"></i> السابق
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Show small step indicator next to the active button for orientation --}}
                        <span class="text-xs font-bold text-slate-400" x-text="'الخطوة ' + step + ' / 3'"></span>

                        <button type="button" x-show="step < 3" wire:click="nextStep" class="px-8 py-3 rounded-xl bg-[#1FA7A2] text-white font-bold shadow-lg shadow-[#1FA7A2]/20 hover:bg-[#167F7B] hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center gap-2">
                            التالي <i class="fas fa-arrow-left rtl:rotate-180"></i>
                        </button>

                        <button type="submit" x-show="step === 3" class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-black shadow-lg shadow-[#1FA7A2]/20 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center gap-2">
                            <i class="fas fa-paper-plane"></i> تقديم الطلب
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>