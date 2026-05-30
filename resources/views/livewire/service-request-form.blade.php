<div class="relative bg-white p-6 md:p-10 rounded-[2.5rem] border border-slate-100 shadow-2xl overflow-hidden group/form transition-all duration-500" 
     dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- 1. Loading State Overlay --}}
    <div wire:loading.flex wire:target="submit" 
         class="absolute inset-0 bg-white/90 z-50 flex flex-col items-center justify-center backdrop-blur-md rounded-[2.5rem]">
        <div class="relative">
            <div class="animate-spin rounded-full h-16 w-16 border-[6px] border-slate-100 border-t-primary-600"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <i class="fas fa-paper-plane text-primary-600 animate-pulse"></i>
            </div>
        </div>
        <p class="mt-4 text-sm font-black text-slate-700 tracking-widest animate-pulse">{{ __('PROCESSING_REQUEST') }}</p>
    </div>

    {{-- 2. Success Feedback (Alpine.js integrated) --}}
    <div x-data="{ show: false, message: '' }"
         x-on:service-request-sent.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 6000)"
         x-show="show" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-90 -translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed top-10 left-1/2 -translate-x-1/2 z-[100] w-[90%] max-w-md p-5 rounded-2xl bg-emerald-600 text-white shadow-2xl flex items-center gap-4"
         style="display:none;">
        <div class="bg-white/20 p-3 rounded-full">
            <i class="fas fa-check-double text-xl"></i>
        </div>
        <div>
            <h4 class="font-black text-base">{{ __('SUCCESS_TITLE') }}</h4>
            <p x-text="message" class="text-xs opacity-90 font-medium"></p>
        </div>
    </div>

    <form wire:submit.prevent="submit" enctype="multipart/form-data" class="space-y-8">
        
        {{-- Section: Applicant Info --}}
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-50 text-primary-600 text-sm font-bold">01</span>
                <h5 class="text-xl font-black text-slate-800 tracking-tight">{{ __('APPLICANT_INFO_SEC') }}</h5>
            </div>

            {{-- Professional Square Cards for Client Type --}}
            <div class="grid grid-cols-2 gap-5">
                {{-- Individual Card --}}
                <button type="button" wire:click="$set('applicant_type', 'person')"
                        class="relative flex flex-col items-center justify-center p-6 sm:p-8 rounded-[2rem] border-2 transition-all duration-300 outline-none group overflow-hidden
                        {{ $applicant_type === 'person' ? 'border-primary-600 bg-primary-50/50 shadow-md scale-[1.02]' : 'border-slate-100 bg-slate-50 hover:border-primary-200 hover:bg-white hover:shadow-sm' }}">

                    {{-- Background Accent (Visible only when active) --}}
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-primary-600 rounded-full blur-3xl transition-opacity duration-300 {{ $applicant_type === 'person' ? 'opacity-20' : 'opacity-0' }}"></div>

                    {{-- Check Indicator --}}
                    <div class="absolute top-4 rtl:right-4 ltr:left-4 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-300
                        {{ $applicant_type === 'person' ? 'border-primary-600 bg-primary-600 text-white scale-100' : 'border-slate-300 bg-transparent text-transparent scale-90 group-hover:border-primary-300' }}">
                        <i class="fas fa-check text-[10px]"></i>
                    </div>

                    {{-- Icon --}}
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-all duration-300 shadow-sm
                        {{ $applicant_type === 'person' ? 'bg-primary-600 text-white' : 'bg-white text-slate-400 group-hover:text-primary-500' }}">
                        <i class="fas fa-user text-2xl"></i>
                    </div>

                    {{-- Text --}}
                    <span class="font-black text-base transition-colors {{ $applicant_type === 'person' ? 'text-primary-700' : 'text-slate-600' }}">
                        {{ __('INDIVIDUAL_CLIENT') }}
                    </span>
                    <span class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">حساب شخصي</span>
                </button>

                {{-- Company Card --}}
                <button type="button" wire:click="$set('applicant_type', 'company')"
                        class="relative flex flex-col items-center justify-center p-6 sm:p-8 rounded-[2rem] border-2 transition-all duration-300 outline-none group overflow-hidden
                        {{ $applicant_type === 'company' ? 'border-primary-600 bg-primary-50/50 shadow-md scale-[1.02]' : 'border-slate-100 bg-slate-50 hover:border-primary-200 hover:bg-white hover:shadow-sm' }}">

                    {{-- Background Accent (Visible only when active) --}}
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-primary-600 rounded-full blur-3xl transition-opacity duration-300 {{ $applicant_type === 'company' ? 'opacity-20' : 'opacity-0' }}"></div>

                    {{-- Check Indicator --}}
                    <div class="absolute top-4 rtl:right-4 ltr:left-4 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-300
                        {{ $applicant_type === 'company' ? 'border-primary-600 bg-primary-600 text-white scale-100' : 'border-slate-300 bg-transparent text-transparent scale-90 group-hover:border-primary-300' }}">
                        <i class="fas fa-check text-[10px]"></i>
                    </div>

                    {{-- Icon --}}
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-all duration-300 shadow-sm
                        {{ $applicant_type === 'company' ? 'bg-primary-600 text-white' : 'bg-white text-slate-400 group-hover:text-primary-500' }}">
                        <i class="fas fa-building text-2xl"></i>
                    </div>

                    {{-- Text --}}
                    <span class="font-black text-base transition-colors {{ $applicant_type === 'company' ? 'text-primary-700' : 'text-slate-600' }}">
                        {{ __('BUSINESS_CLIENT') }}
                    </span>
                    <span class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">تأسيس/مؤسسة</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                {{-- Name Field --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('FULL_NAME_LABEL') }}</label>
                    <div class="group relative flex items-center bg-slate-50 border-2 border-slate-100 rounded-2xl transition-all duration-300 focus-within:border-primary-600 focus-within:bg-white focus-within:ring-4 focus-within:ring-primary-50">
                        <i class="fas fa-signature absolute left-4 rtl:right-4 text-slate-300 group-focus-within:text-primary-600 transition-colors"></i>
                        <input type="text" wire:model.blur="applicant_name" autocomplete="name"
                               class="w-full bg-transparent border-none py-4 px-12 outline-none text-slate-800 font-bold placeholder-slate-300 text-sm focus:ring-0" 
                               placeholder="{{ __('NAME_PLACEHOLDER') }}">
                    </div>
                    @error('applicant_name') <span class="text-rose-500 text-[10px] font-bold px-2 italic">{{ $message }}</span> @enderror
                </div>

                {{-- Phone Field --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('PHONE_LABEL') }}</label>
                    <div class="group relative flex items-center bg-slate-50 border-2 border-slate-100 rounded-2xl transition-all duration-300 focus-within:border-primary-600 focus-within:bg-white focus-within:ring-4 focus-within:ring-primary-50">
                        <i class="fas fa-mobile-alt absolute left-4 rtl:right-4 text-slate-300 group-focus-within:text-primary-600 transition-colors"></i>
                        <input type="tel" wire:model.blur="phone" dir="ltr" autocomplete="tel"
                               class="w-full bg-transparent border-none py-4 px-12 outline-none text-slate-800 font-bold placeholder-slate-300 text-sm focus:ring-0" 
                               placeholder="05xxxxxxxx">
                    </div>
                    @error('phone') <span class="text-rose-500 text-[10px] font-bold px-2 italic">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Section: Service Details --}}
        <div class="space-y-6 pt-4 border-t border-slate-100">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-50 text-primary-600 text-sm font-bold">02</span>
                <h5 class="text-xl font-black text-slate-800 tracking-tight">{{ __('SERVICE_DETAILS_SEC') }}</h5>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Business Info (Conditional) --}}
                @if($applicant_type === 'company')
                <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('BUSINESS_NAME_LABEL') }}</label>
                        <div class="group relative flex items-center bg-slate-50 border-2 border-slate-100 rounded-2xl transition-all duration-300 focus-within:border-primary-600 focus-within:bg-white focus-within:ring-4 focus-within:ring-primary-50">
                            <i class="fas fa-store absolute left-4 rtl:right-4 text-slate-300 group-focus-within:text-primary-600 transition-colors"></i>
                            <input type="text" wire:model.defer="establishment_name" 
                                   class="w-full bg-transparent border-none py-4 px-12 outline-none text-slate-800 font-bold placeholder-slate-300 text-sm focus:ring-0"
                                   placeholder="{{ __('BUSINESS_NAME_PLACEHOLDER') }}">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('CR_NUMBER_LABEL') }}</label>
                        <div class="group relative flex items-center bg-slate-50 border-2 border-slate-100 rounded-2xl transition-all duration-300 focus-within:border-primary-600 focus-within:bg-white focus-within:ring-4 focus-within:ring-primary-50">
                            <i class="fas fa-id-card absolute left-4 rtl:right-4 text-slate-300 group-focus-within:text-primary-600 transition-colors"></i>
                            <input type="text" wire:model.defer="cr_number" dir="ltr"
                                   class="w-full bg-transparent border-none py-4 px-12 outline-none text-slate-800 font-bold placeholder-slate-300 text-sm focus:ring-0"
                                   placeholder="70xxxxxxxx">
                        </div>
                    </div>
                </div>
                @endif

                {{-- Service Selector --}}
                <div class="col-span-1 md:col-span-2 space-y-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('SERVICE_TYPE_LABEL') }}</label>
                    <div class="relative group">
                        <select wire:model.live="service_id" 
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-4 px-12 outline-none text-slate-800 font-bold appearance-none cursor-pointer transition-all focus:border-primary-600 focus:bg-white focus:ring-4 focus:ring-primary-50 text-sm">
                            <option value="">{{ __('SELECT_SERVICE_DEFAULT') }}</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">
                                    {{ $service->{'title_'.app()->getLocale()} ?? $service->title_ar ?? $service->title_en ?? $service->slug }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-layer-group absolute left-4 rtl:right-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-600 transition-colors pointer-events-none"></i>
                        <i class="fas fa-chevron-down absolute right-4 rtl:left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none group-hover:translate-y-[-40%] transition-transform"></i>
                    </div>
                    @error('service_id') <span class="text-rose-500 text-[10px] font-bold px-2 italic">{{ $message }}</span> @enderror
                </div>

                {{-- Notes --}}
                <div class="col-span-1 md:col-span-2 space-y-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('NOTES_LABEL') }}</label>
                    <textarea wire:model.defer="notes" rows="4" 
                              class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-6 outline-none text-slate-800 font-bold transition-all focus:border-primary-600 focus:bg-white focus:ring-4 focus:ring-primary-50 text-sm resize-none placeholder:text-slate-300"
                              placeholder="{{ __('NOTES_PLACEHOLDER') }}"></textarea>
                </div>

                {{-- File Upload --}}
                <div class="col-span-1 md:col-span-2 space-y-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('UPLOAD_SEC_LABEL') }}</label>
                    <div class="relative group/upload border-2 border-dashed border-slate-200 bg-slate-50 rounded-[2rem] p-8 text-center transition-all duration-300 hover:border-primary-600 hover:bg-primary-50/30 cursor-pointer overflow-hidden">
                        <input type="file" wire:model="attachment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept=".pdf,.jpg,.jpeg,.png">
                        
                        <div class="space-y-3">
                            <div class="w-14 h-14 mx-auto bg-white rounded-2xl shadow-sm flex items-center justify-center text-slate-400 group-hover/upload:text-primary-600 group-hover/upload:rotate-12 transition-all">
                                <i class="fas fa-cloud-upload-alt text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-700 group-hover/upload:text-primary-600">{{ __('CLICK_TO_UPLOAD') }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">{{ __('UPLOAD_HINT') }}</p>
                            </div>
                        </div>

                        @if($attachment)
                        <div class="mt-4 animate-bounce-in inline-flex items-center gap-2 bg-emerald-500 text-white px-4 py-1.5 rounded-full text-[10px] font-black shadow-lg shadow-emerald-200">
                            <i class="fas fa-file-check"></i> {{ $attachment->getClientOriginalName() }}
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Terms Checkbox --}}
                <div class="col-span-1 md:col-span-2 pt-2">
                    <label class="flex items-start gap-3 cursor-pointer group/check">
                        <div class="relative flex items-center">
                            <input type="checkbox" wire:model="agreed_terms" class="peer sr-only">
                            <div class="w-6 h-6 border-2 border-slate-200 rounded-lg transition-all peer-checked:bg-primary-600 peer-checked:border-primary-600 shadow-sm"></div>
                            <i class="fas fa-check absolute inset-0 m-auto text-white text-[10px] scale-0 peer-checked:scale-100 transition-transform text-center pt-1.5"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-400 leading-relaxed transition-colors group-hover/check:text-slate-600">
                            {{ __('AGREE_TO_TERMS_START') }} 
                            <a href="{{ url('/terms') }}" class="text-primary-600 hover:underline underline-offset-4">{{ __('TERMS_LINK_TEXT') }}</a>
                        </span>
                    </label>
                    @error('agreed_terms') <span class="text-rose-500 text-[10px] font-bold mt-1 block italic">{{ $message }}</span> @enderror
                </div>

                {{-- Submit Action --}}
                <div class="col-span-1 md:col-span-2 pt-6">
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="relative w-full group/btn overflow-hidden py-5 rounded-2xl bg-slate-900 text-white font-black text-base shadow-xl transition-all duration-300 hover:bg-primary-600 hover:-translate-y-1 hover:shadow-primary-200 active:scale-95 disabled:opacity-50">
                        
                        <div class="relative z-10 flex items-center justify-center gap-3">
                            <span wire:loading.remove>{{ __('SUBMIT_ORDER_BTN') }}</span>
                            <span wire:loading>{{ __('SUBMITTING_TEXT') }}</span>
                            <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} group-hover/btn:translate-x-{{ app()->getLocale() == 'ar' ? '-5' : '5' }} transition-transform duration-300"></i>
                        </div>

                        {{-- Hover Effect Glow --}}
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-primary-400 to-primary-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
