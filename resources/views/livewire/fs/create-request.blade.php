<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    
    {{-- Main Form Column --}}
    <div class="lg:col-span-7">
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 md:p-10">
            <h5 class="text-2xl font-black text-slate-900 mb-8 flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="w-10 h-10 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2]">
                    <i class="fas fa-file-invoice"></i>
                </span>
                {{ __('request_details') }}
            </h5>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Company Name --}}
                <div class="group relative">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('label_company_name') }}</label>
                    <input type="text" 
                           class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-4 font-bold outline-none transition-all shadow-sm placeholder-slate-400"
                           wire:model.defer="company_name" 
                           placeholder="{{ __('placeholder_company_name') }}">
                </div>

                {{-- CR Number --}}
                <div class="group relative">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('label_cr_number') }}</label>
                    <input type="text" 
                           class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-4 font-bold outline-none transition-all shadow-sm placeholder-slate-400 font-mono"
                           wire:model.defer="commercial_registration_no" 
                           placeholder="1010xxxxxx">
                </div>

                {{-- Fiscal Year --}}
                <div class="group relative">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('label_fiscal_year') }}</label>
                    <input type="text" 
                           class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-4 font-bold outline-none transition-all shadow-sm placeholder-slate-400 font-mono"
                           wire:model.defer="fiscal_year" 
                           placeholder="2025">
                </div>

                {{-- Notes --}}
                <div class="md:col-span-2 group relative">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('label_additional_notes') }}</label>
                    <textarea rows="4" 
                              class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-4 font-medium outline-none transition-all shadow-sm placeholder-slate-400 resize-none"
                              wire:model.defer="client_notes" 
                              placeholder="{{ __('placeholder_notes') }}"></textarea>
                </div>
            </div>

            @error('files') <div class="text-red-500 text-xs font-bold mt-4 bg-red-50 p-3 rounded-lg border border-red-100">{{ $message }}</div> @enderror

            <div class="mt-8 pt-6 border-t border-slate-100">
                <button class="w-full py-4 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold text-lg shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/20 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:click="submit" 
                        wire:loading.attr="disabled">
                    
                    <span wire:loading.remove>{{ __('btn_submit_request') }}</span>
                    
                    <div wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('loading_sending') }}
                    </div>
                </button>
            </div>
        </div>
    </div>

    {{-- File Upload Column --}}
    <div class="lg:col-span-5">
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 md:p-10 sticky top-8">
            <h5 class="text-2xl font-black text-slate-900 mb-2">{{ __('upload_files_title') }}</h5>
            <p class="text-slate-500 text-sm mb-6">{{ __('upload_files_desc') }}</p>

            <div class="flex flex-col gap-4">
                @foreach($kinds as $key => $label)
                    @php $isReq = in_array($key, $required, true); @endphp
                    
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-white hover:border-[#1FA7A2]/30 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-slate-800 font-bold text-sm group-hover:text-[#1FA7A2] transition-colors">
                                {{ __($label) }}
                            </div>
                            @if($isReq)
                                <span class="px-2 py-1 rounded text-[10px] font-bold bg-red-50 text-red-600 border border-red-100">{{ __('badge_required') }}</span>
                            @else
                                <span class="px-2 py-1 rounded text-[10px] font-bold bg-slate-200 text-slate-500">{{ __('badge_optional') }}</span>
                            @endif
                        </div>

                        <div class="relative">
                            <input type="file" 
                                   class="block w-full text-xs text-slate-500
                                          file:mr-0 file:ml-2 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-xs file:font-bold
                                          file:bg-[#1FA7A2] file:text-white
                                          hover:file:bg-[#167F7B]
                                          cursor-pointer border border-slate-300 rounded-lg bg-white h-10 pt-1.5 px-2 focus:outline-none focus:border-[#1FA7A2]"
                                   wire:model="files.{{ $key }}"
                                   multiple
                                   accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png,.webp">
                        </div>

                        @error("files.$key.*") 
                            <div class="text-red-500 text-xs font-bold mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div> 
                        @enderror

                        <div class="text-slate-400 text-[10px] font-medium mt-2 flex items-center gap-1">
                            <i class="fas fa-info-circle"></i> PDF / Excel / Images — Max 20MB
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>