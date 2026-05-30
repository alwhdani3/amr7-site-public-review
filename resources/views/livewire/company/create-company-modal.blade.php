<div x-data="{ 
        open: @entangle('open'),
        closeModal() {
            this.open = false;
            setTimeout(() => $wire.call('closeModal'), 300);
        }
     }" 
     x-on:keydown.escape.window="closeModal()"
     x-cloak
     class="relative z-50">

    <div class="text-center mt-6">
        <button type="button"
                @click="open = true"
                class="add-company-trigger group inline-flex items-center gap-3 px-6 py-3 rounded-2xl border border-dashed border-slate-300 bg-white text-slate-600 font-bold hover:border-[#1FA7A2] hover:text-[#1FA7A2] hover:bg-[#1FA7A2]/5 hover:shadow-md transition-all duration-300">
            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center group-hover:bg-[#1FA7A2] group-hover:text-white transition-colors duration-300">
                <i class="fas fa-plus text-sm"></i>
            </div>
            <span>{{ __('btn_add_new_company') }}</span>
        </button>
    </div>

    <div x-show="open" 
         x-transition.opacity.duration.300ms
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
         @click="closeModal()"></div>

    <div x-show="open" class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            
                <div x-show="open"
                 x-trap.noscroll="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform flex flex-col rounded-[2rem] bg-white text-start shadow-2xl transition-all my-4 sm:my-8 w-[min(92vw,720px)] sm:w-full sm:max-w-2xl lg:max-w-3xl max-h-[92vh] border border-slate-100 overflow-hidden"
                 dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                
                <div class="bg-slate-50/50 px-6 sm:px-8 py-5 sm:py-6 border-b border-slate-100 flex justify-between items-start shrink-0">
                    <div class="min-w-0">
                        <h3 class="text-lg sm:text-xl font-black text-slate-800 flex items-center gap-3">
                            <span class="w-10 h-10 shrink-0 rounded-xl bg-[#1FA7A2]/10 text-[#1FA7A2] flex items-center justify-center shadow-sm">
                                <i class="fas fa-building text-base"></i>
                            </span>
                            {{ __('modal_new_company_title') }}
                        </h3>
                        <p class="text-slate-500 text-xs sm:text-sm font-bold mt-2 rtl:pr-14 ltr:pl-14">{{ __('modal_new_company_desc') }}</p>
                    </div>
                    <button type="button"
                            aria-label="إغلاق"
                            class="w-9 h-9 shrink-0 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-rose-500 hover:bg-rose-50 flex items-center justify-center transition-all shadow-sm"
                            @click="closeModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6 sm:p-8 overflow-y-auto">
                    <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="col-span-1 md:col-span-2 space-y-2">
                            <label class="text-xs font-black tracking-widest text-slate-500 uppercase px-1">
                                {{ __('label_company_name') }} <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative group">
                                <div class="absolute rtl:right-4 ltr:left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none">
                                    <i class="fas fa-signature"></i>
                                </div>
                                <input type="text" 
                                       wire:model.blur="name" 
                                       class="w-full bg-slate-50 border-2 border-slate-100 focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-2xl rtl:pr-11 rtl:pl-4 ltr:pl-11 ltr:pr-4 py-3.5 text-slate-900 font-bold outline-none transition-all placeholder-slate-300 text-sm" 
                                       placeholder="{{ __('placeholder_company_name') }}">
                            </div>
                            @error('name') <span class="text-rose-500 text-xs font-bold px-2 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black tracking-widest text-slate-500 uppercase px-1">
                                {{ __('label_unified_number_700') }}
                            </label>
                            <div class="relative group">
                                <div class="absolute rtl:right-4 ltr:left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <input type="text" 
                                       wire:model.blur="unified_number" 
                                       class="w-full bg-slate-50 border-2 border-slate-100 focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-2xl rtl:pr-11 rtl:pl-4 ltr:pl-11 ltr:pr-4 py-3.5 text-slate-900 font-bold outline-none transition-all placeholder-slate-300 font-mono text-left text-sm" 
                                       placeholder="700XXXXXXXXX"
                                       dir="ltr">
                            </div>
                            @error('unified_number') <span class="text-rose-500 text-xs font-bold px-2 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black tracking-widest text-slate-500 uppercase px-1">
                                {{ __('label_tax_number') }}
                            </label>
                            <div class="relative group">
                                <div class="absolute rtl:right-4 ltr:left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none">
                                    <i class="fas fa-percent"></i>
                                </div>
                                <input type="text" 
                                       wire:model.blur="tax_number" 
                                       class="w-full bg-slate-50 border-2 border-slate-100 focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-2xl rtl:pr-11 rtl:pl-4 ltr:pl-11 ltr:pr-4 py-3.5 text-slate-900 font-bold outline-none transition-all placeholder-slate-300 font-mono text-left text-sm" 
                                       placeholder="3xxxxxxxxxxxxx3" 
                                       dir="ltr">
                            </div>
                            @error('tax_number') <span class="text-rose-500 text-xs font-bold px-2 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black tracking-widest text-slate-500 uppercase px-1">
                                {{ __('label_city') }}
                            </label>
                            <div class="relative group">
                                <div class="absolute rtl:right-4 ltr:left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <input type="text" 
                                       wire:model.blur="city" 
                                       class="w-full bg-slate-50 border-2 border-slate-100 focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-2xl rtl:pr-11 rtl:pl-4 ltr:pl-11 ltr:pr-4 py-3.5 text-slate-900 font-bold outline-none transition-all placeholder-slate-300 text-sm" 
                                       placeholder="{{ __('placeholder_city') }}">
                            </div>
                            @error('city') <span class="text-rose-500 text-xs font-bold px-2 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black tracking-widest text-slate-500 uppercase px-1">
                                {{ __('label_national_address') }}
                            </label>
                            <div class="relative group">
                                <div class="absolute rtl:right-4 ltr:left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none">
                                    <i class="fas fa-map-pin"></i>
                                </div>
                                <input type="text" 
                                       wire:model.blur="address" 
                                       class="w-full bg-slate-50 border-2 border-slate-100 focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-2xl rtl:pr-11 rtl:pl-4 ltr:pl-11 ltr:pr-4 py-3.5 text-slate-900 font-bold outline-none transition-all placeholder-slate-300 text-sm" 
                                       placeholder="{{ __('placeholder_address') }}">
                            </div>
                             @error('address') <span class="text-rose-500 text-xs font-bold px-2 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-1 md:col-span-2 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 mt-4 pt-6 border-t border-slate-100">
                            <button type="button"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 rounded-2xl bg-white border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 hover:text-slate-800 hover:border-slate-300 transition-colors"
                                    @click="closeModal()">
                                {{ __('btn_cancel') }}
                            </button>

                            <button type="submit"
                                    class="relative w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-[#1FA7A2] text-white font-black shadow-lg shadow-[#1FA7A2]/20 hover:shadow-[#1FA7A2]/40 hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed group/btn"
                                    wire:loading.attr="disabled"
                                    wire:target="save">
                                <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                                    {{ __('add_company_submit') ?: 'إضافة المنشأة' }}
                                    <i class="fas fa-arrow-left text-xs transition-transform group-hover/btn:-translate-x-1"></i>
                                </span>
                                <span wire:loading.flex wire:target="save" class="items-center gap-2">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                    {{ __('processing') }}
                                </span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
