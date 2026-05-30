<div class="animate__animated animate__fadeIn font-['Tajawal']" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border-s-4 border-emerald-500 p-4 rounded-xl shadow-sm flex items-center gap-3 animate__animated animate__bounceIn">
            <div class="p-2 bg-emerald-100 rounded-full text-emerald-600">
                <i class="fas fa-check"></i>
            </div>
            <div class="text-slate-700 font-bold text-sm">{{ session('success') }}</div>
        </div>
    @endif

    <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-100 p-6 md:p-8 mb-8 relative overflow-hidden group">
        <div class="absolute top-0 end-0 p-4 opacity-[0.03] group-hover:opacity-[0.05] transition-opacity pointer-events-none">
            <i class="fas fa-building text-9xl text-[#1FA7A2]"></i>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 pb-6 border-b border-slate-100 gap-4 relative z-10">
            <h5 class="text-xl md:text-2xl font-black text-slate-800 flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shadow-sm">
                    <i class="fas fa-building text-xl"></i>
                </div>
                {{ __('company_profile_title') }}
            </h5>
            
            <button class="px-5 py-2.5 rounded-xl bg-slate-50 text-slate-600 text-sm font-bold border border-slate-200 hover:border-[#1FA7A2] hover:text-[#1FA7A2] hover:bg-white transition-all duration-300 flex items-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" 
                    wire:click="$toggle('showEditCompany')" 
                    @disabled(!$company)>
                <i class="fas fa-pen-to-square"></i> {{ __('btn_edit_info') }}
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 relative z-10">
            <div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-4 hover:border-[#1FA7A2]/20 hover:bg-white hover:shadow-md transition-all duration-300">
                <small class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1.5">{{ __('label_company_name') }}</small>
                <div class="flex items-center gap-2">
                    <strong class="text-slate-800 text-sm md:text-base block truncate">{{ $company->name ?? '---' }}</strong>
                    @if($company) <i class="fas fa-check-circle text-emerald-500 text-xs" title="{{ __('Verified') }}"></i> @endif
                </div>
            </div>
            
            <div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-4 hover:border-[#1FA7A2]/20 hover:bg-white hover:shadow-md transition-all duration-300">
                <small class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1.5">{{ __('label_unified_number') }}</small>
                <strong class="text-slate-800 text-sm md:text-base font-mono block tracking-wide">{{ $company->unified_number ?? '---' }}</strong>
            </div>

            <div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-4 hover:border-[#1FA7A2]/20 hover:bg-white hover:shadow-md transition-all duration-300">
                <small class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1.5">{{ __('label_tax_number') }}</small>
                <strong class="text-slate-800 text-sm md:text-base font-mono block tracking-wide">{{ $company->tax_number ?? '---' }}</strong>
            </div>

            <div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-4 hover:border-[#1FA7A2]/20 hover:bg-white hover:shadow-md transition-all duration-300">
                <small class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1.5">{{ __('label_role') }}</small>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#1FA7A2]/10 text-[#1FA7A2] text-xs font-bold">
                    <i class="fas fa-user-shield text-[10px]"></i>
                    {{ $company?->pivot?->role ?? __('role_owner') }}
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-100 p-6 md:p-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 pb-6 border-b border-slate-100 gap-4">
            <h5 class="text-xl md:text-2xl font-black text-slate-800 flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shadow-sm">
                    <i class="fas fa-folder-open text-xl"></i>
                </div>
                {{ __('docs_section_title') }}
            </h5>
            <button class="px-6 py-2.5 rounded-xl bg-[#1FA7A2] text-white text-sm font-bold shadow-lg shadow-[#1FA7A2]/20 hover:bg-[#167F7B] hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" 
                    wire:click="$toggle('showAddDocument')" 
                    @disabled(!$company && !$isAdmin)>
                <i class="fas fa-plus"></i> {{ __('btn_add_doc') }}
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex justify-between items-center group cursor-default">
                <div>
                    <small class="text-slate-400 font-bold block mb-1 text-xs">{{ __('stats_total') }}</small>
                    <strong class="text-slate-800 text-2xl font-black group-hover:text-[#1FA7A2] transition-colors">{{ $this->stats['total'] }}</strong>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-50 group-hover:bg-[#1FA7A2]/10 flex items-center justify-center text-slate-300 group-hover:text-[#1FA7A2] transition-colors">
                    <i class="fas fa-layer-group text-xl"></i>
                </div>
            </div>
            
            <div class="bg-white border border-emerald-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex justify-between items-center group cursor-default">
                <div>
                    <small class="text-emerald-600/70 font-bold block mb-1 text-xs">{{ __('stats_valid') }}</small>
                    <strong class="text-slate-800 text-2xl font-black">{{ $this->stats['valid'] }}</strong>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 group-hover:bg-emerald-100 flex items-center justify-center text-emerald-500">
                    <i class="fas fa-check-double text-xl"></i>
                </div>
            </div>

            <div class="bg-white border border-amber-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex justify-between items-center group cursor-default">
                <div>
                    <small class="text-amber-600/70 font-bold block mb-1 text-xs">{{ __('stats_warning') }}</small>
                    <strong class="text-slate-800 text-2xl font-black">{{ $this->stats['warning'] }}</strong>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 group-hover:bg-amber-100 flex items-center justify-center text-amber-500 animate-pulse">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
            </div>

            <div class="bg-white border border-red-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex justify-between items-center group cursor-default">
                <div>
                    <small class="text-red-600/70 font-bold block mb-1 text-xs">{{ __('stats_expired') }}</small>
                    <strong class="text-slate-800 text-2xl font-black">{{ $this->stats['expired'] }}</strong>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-50 group-hover:bg-red-100 flex items-center justify-center text-red-500">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
            </div>
        </div>

        @if($this->documents->count() === 0)
            <div class="text-center py-16 bg-slate-50/50 rounded-[2rem] border-2 border-dashed border-slate-200 hover:border-[#1FA7A2]/30 transition-colors">
                <div class="mb-4 text-slate-200">
                    <i class="fas fa-folder-open fa-4x"></i>
                </div>
                <p class="text-slate-500 font-bold text-base mb-6">{{ __('no_docs_msg') }}</p>
                <button class="px-6 py-2.5 rounded-xl border border-[#1FA7A2] text-[#1FA7A2] font-bold hover:bg-[#1FA7A2] hover:text-white transition-all duration-300 shadow-sm" 
                        wire:click="$toggle('showAddDocument')" 
                        @disabled(!$company && !$isAdmin)>
                    <i class="fas fa-plus me-2"></i> {{ __('btn_add_first_doc') }}
                </button>
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-slate-100 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-start text-slate-500">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-50/80 border-b border-slate-100">
                            <tr>
                                @if($isAdmin) <th class="px-6 py-4 font-bold text-start">{{ __('table_company') }}</th> @endif
                                <th class="px-6 py-4 font-bold text-start">{{ __('table_type') }}</th>
                                <th class="px-6 py-4 font-bold text-start">{{ __('table_doc_num') }}</th>
                                <th class="px-6 py-4 font-bold text-start">{{ __('table_issue_date') }}</th>
                                <th class="px-6 py-4 font-bold text-start">{{ __('table_expiry_date') }}</th>
                                <th class="px-6 py-4 font-bold text-start">{{ __('table_status') }}</th>
                                <th class="px-6 py-4 font-bold text-start">{{ __('table_file') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                        @foreach($this->documents as $doc)
                            @php
                                $badgeStyle = match($doc->status){
                                    'valid' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                    'warning' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    'expired' => 'bg-red-50 text-red-600 border-red-100',
                                    default => 'bg-slate-50 text-slate-500 border-slate-100'
                                };
                            @endphp
                            <tr class="hover:bg-[#f8fafc] transition-colors group">
                                @if($isAdmin)
                                    <td class="px-6 py-4 text-start">
                                        <div class="font-bold text-slate-800">{{ $doc->company->name ?? '-' }}</div>
                                    </td>
                                @endif
                                <td class="px-6 py-4 text-start">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-[#1FA7A2]"></span>
                                        <span class="font-bold text-slate-700">{{ $doc->type_label ?? $doc->type }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-slate-600 font-bold text-start">{{ $doc->document_number ?? '-' }}</td>
                                <td class="px-6 py-4 font-mono text-slate-500 text-start">{{ optional($doc->issue_date)->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-6 py-4 font-mono text-slate-500 text-start">{{ optional($doc->expiry_date)->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-6 py-4 text-start">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border {{ $badgeStyle }}">
                                        {{ __('status_' . $doc->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-start">
                                    @if($doc->file_path)
                                        <a class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] hover:shadow-md transition-all duration-200" 
                                           href="{{ route('company.docs.download', $doc) }}" 
                                           target="_blank" 
                                           title="{{ __('btn_download') }}">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @else
                                        <span class="text-slate-300 text-xs italic">{{ __('no_file') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <div x-data="{ open: @entangle('showEditCompany') }" x-cloak>
        <div x-show="open" class="relative z-[9999]" role="dialog" aria-modal="true">
            <div x-show="open" 
                 x-transition.opacity.duration.300ms
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                 @click="open = false"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="open"
                         x-trap.noscroll="open"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                         class="relative transform overflow-hidden rounded-[2rem] bg-white text-start shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-100">
                        
                        <div class="bg-slate-50/80 px-8 py-5 border-b border-slate-100 flex justify-between items-center">
                            <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] text-sm">
                                    <i class="fas fa-pen"></i>
                                </span>
                                {{ __('modal_edit_title') }}
                            </h3>
                            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors p-2" @click="open = false">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                        
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="md:col-span-2 space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('label_company_name') }}</label>
                                    <input type="text" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none transition-all placeholder-slate-400" wire:model.defer="name">
                                    @error('name') <span class="text-red-500 text-xs font-bold block">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('label_unified_number') }}</label>
                                    <input type="text" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none transition-all font-mono placeholder-slate-400" wire:model.defer="unified_number">
                                    @error('unified_number') <span class="text-red-500 text-xs font-bold block">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('label_tax_number') }}</label>
                                    <input type="text" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none transition-all font-mono placeholder-slate-400" wire:model.defer="tax_number">
                                    @error('tax_number') <span class="text-red-500 text-xs font-bold block">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('label_city') }}</label>
                                    <input type="text" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none transition-all placeholder-slate-400" wire:model.defer="city">
                                </div>
                                <div class="md:col-span-2 space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('label_address') }}</label>
                                    <input type="text" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none transition-all placeholder-slate-400" wire:model.defer="address">
                                </div>
                            </div>
                            
                            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                                <button type="button" class="px-6 py-2.5 rounded-xl text-slate-500 font-bold hover:bg-slate-50 transition-colors" @click="open = false">{{ __('btn_cancel') }}</button>
                                <button type="button" class="px-8 py-2.5 rounded-xl bg-[#1FA7A2] text-white font-bold shadow-lg shadow-[#1FA7A2]/20 hover:bg-[#167F7B] hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2" 
                                        wire:click="saveCompany" 
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove>{{ __('btn_save') }}</span>
                                    <span wire:loading><i class="fas fa-circle-notch fa-spin"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stage 0: x-on:* explicit (لا @ prefix) + state بسيط + بلا comma-separated wire:target --}}
    <div x-data="{ open: @entangle('showAddDocument'), uploading: false, progress: 0, uploadError: '' }"
         x-on:livewire-upload-start.window="uploading = true; progress = 0; uploadError = ''"
         x-on:livewire-upload-progress.window="progress = ($event.detail?.progress ?? 0)"
         x-on:livewire-upload-finish.window="uploading = false; progress = 100"
         x-on:livewire-upload-cancel.window="uploading = false; progress = 0"
         x-on:livewire-upload-error.window="uploading = false; progress = 0; uploadError = 'تعذّر رفع الملف. حاول مرة أخرى.'"
         x-on:keydown.escape.window="if(open && !uploading) open = false"
         wire:key="documents-panel-add-modal"
         x-cloak>
        <div x-show="open" class="relative z-[9999]" role="dialog" aria-modal="true">
            <div x-show="open"
                 x-transition.opacity.duration.300ms
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                 @click="if(!uploading) open = false"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="open"
                         x-trap.noscroll="open"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                         class="relative transform overflow-hidden rounded-[2rem] bg-white text-start shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-100">
                        
                        <div class="bg-slate-50/80 px-8 py-5 border-b border-slate-100 flex justify-between items-center">
                            <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] text-sm">
                                    <i class="fas fa-folder-plus"></i>
                                </span>
                                {{ __('modal_add_doc_title') }}
                            </h3>
                            <button type="button" :disabled="uploading" class="text-slate-400 hover:text-red-500 transition-colors p-2 disabled:opacity-50 disabled:cursor-not-allowed" @click="if(!uploading) open = false">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                        
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                @if($isAdmin)
                                    <div class="md:col-span-2 space-y-1.5">
                                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('label_select_company') }}</label>
                                        <div class="relative">
                                            <select class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none appearance-none cursor-pointer" wire:model.defer="company_id">
                                                <option value="">{{ __('select_placeholder') }}</option>
                                                @foreach(\App\Models\Company::orderBy('name')->get() as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                            <i class="fas fa-chevron-down absolute end-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                                        </div>
                                        @error('company_id') <span class="text-red-500 text-xs font-bold block">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                                
                                {{-- Document type chips — keys match what AI extraction supports
                                     (cr / articles_of_association / license / id / tax / other). --}}
                                <div class="space-y-1.5 md:col-span-2"
                                     x-data="{
                                         types: [
                                             { v: 'cr',                      label: 'سجل تجاري',     icon: 'fa-file-contract' },
                                             { v: 'articles_of_association', label: 'عقد تأسيس',    icon: 'fa-scroll' },
                                             { v: 'license',                 label: 'رخصة',          icon: 'fa-id-badge' },
                                             { v: 'id',                      label: 'هوية',          icon: 'fa-id-card' },
                                             { v: 'tax',                     label: 'شهادة ضريبية', icon: 'fa-receipt' },
                                             { v: 'other',                   label: 'أخرى',          icon: 'fa-folder' },
                                         ]
                                     }">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('label_doc_type') }}</label>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        <template x-for="t in types" :key="t.v">
                                            <button type="button"
                                                    @click="$wire.set('type', t.v)"
                                                    :class="$wire.type === t.v
                                                        ? 'bg-[#1FA7A2] text-white border-[#1FA7A2] shadow-sm'
                                                        : 'bg-white text-slate-600 border-slate-200 hover:border-[#1FA7A2]/40 hover:bg-[#1FA7A2]/5'"
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border text-xs font-bold transition-all">
                                                <i :class="'fas ' + t.icon + ' text-[11px]'"></i>
                                                <span x-text="t.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                    {{-- Fallback hidden input so server-side validation has the value bound
                                         even if Alpine fails to mount. --}}
                                    <input type="hidden" wire:model.defer="type">
                                    @error('type') <span class="text-red-500 text-xs font-bold block">{{ $message }}</span> @enderror
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('label_doc_num_opt') }}</label>
                                    <input type="text" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none transition-all font-mono" wire:model.defer="document_number">
                                </div>

                                {{-- Issue date with quick chips (today / yesterday / 1m / 6m / 1y ago).
                                     Native date input stays as the fallback / manual entry. --}}
                                <div class="space-y-1.5"
                                     x-data="{
                                         setOffsetDays(days) {
                                             const d = new Date();
                                             d.setDate(d.getDate() - days);
                                             @this.set('issue_date', d.toISOString().slice(0,10));
                                         },
                                         setOffsetMonths(months) {
                                             const d = new Date();
                                             d.setMonth(d.getMonth() - months);
                                             @this.set('issue_date', d.toISOString().slice(0,10));
                                         },
                                         setOffsetYears(years) {
                                             const d = new Date();
                                             d.setFullYear(d.getFullYear() - years);
                                             @this.set('issue_date', d.toISOString().slice(0,10));
                                         }
                                     }">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('label_issue_date_opt') }}</label>
                                    <div class="flex flex-wrap gap-1.5 mt-1">
                                        <button type="button" @click="setOffsetDays(0)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#1FA7A2]/10 text-slate-700 hover:text-[#1FA7A2] text-[11px] font-bold border border-slate-200 hover:border-[#1FA7A2]/40 transition-colors">اليوم</button>
                                        <button type="button" @click="setOffsetDays(1)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#1FA7A2]/10 text-slate-700 hover:text-[#1FA7A2] text-[11px] font-bold border border-slate-200 hover:border-[#1FA7A2]/40 transition-colors">أمس</button>
                                        <button type="button" @click="setOffsetMonths(1)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#1FA7A2]/10 text-slate-700 hover:text-[#1FA7A2] text-[11px] font-bold border border-slate-200 hover:border-[#1FA7A2]/40 transition-colors">قبل شهر</button>
                                        <button type="button" @click="setOffsetMonths(6)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#1FA7A2]/10 text-slate-700 hover:text-[#1FA7A2] text-[11px] font-bold border border-slate-200 hover:border-[#1FA7A2]/40 transition-colors">قبل 6 أشهر</button>
                                        <button type="button" @click="setOffsetYears(1)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#1FA7A2]/10 text-slate-700 hover:text-[#1FA7A2] text-[11px] font-bold border border-slate-200 hover:border-[#1FA7A2]/40 transition-colors">قبل سنة</button>
                                    </div>
                                    {{-- Stage 3: dir=ltr لمنع تشوّه التاريخ في RTL --}}
                                    <input type="date" dir="ltr" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none transition-all text-start" wire:model.defer="issue_date">
                                </div>

                                <div class="space-y-1.5"
                                     x-data="{
                                        noExpiry: @entangle('expiry_date').defer === null || @entangle('expiry_date').defer === '',
                                        setMonths(m) {
                                            const d = new Date();
                                            d.setMonth(d.getMonth() + m);
                                            this.noExpiry = false;
                                            @this.set('expiry_date', d.toISOString().slice(0,10));
                                        },
                                        setYears(y) {
                                            const d = new Date();
                                            d.setFullYear(d.getFullYear() + y);
                                            this.noExpiry = false;
                                            @this.set('expiry_date', d.toISOString().slice(0,10));
                                        },
                                        clearExpiry() {
                                            this.noExpiry = true;
                                            @this.set('expiry_date', '');
                                        }
                                     }">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center justify-between gap-2 flex-wrap">
                                        <span>{{ __('label_expiry_date') }} <span class="text-slate-400 normal-case font-normal">(اختياري)</span></span>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer normal-case">
                                            <input type="checkbox" x-model="noExpiry" @change="if (noExpiry) clearExpiry()" class="rounded border-slate-300 text-[#1FA7A2] focus:ring-[#1FA7A2]/30">
                                            <span class="text-[11px] font-bold text-slate-500">لا يوجد تاريخ انتهاء</span>
                                        </label>
                                    </label>

                                    {{-- Quick expiry shortcuts (AMR7 navy/teal palette only) --}}
                                    <div class="flex flex-wrap gap-1.5 mt-1" x-show="!noExpiry">
                                        <button type="button" @click="setMonths(3)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#1FA7A2]/10 text-slate-700 hover:text-[#1FA7A2] text-[11px] font-bold border border-slate-200 hover:border-[#1FA7A2]/40 transition-colors">3 أشهر</button>
                                        <button type="button" @click="setMonths(6)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#1FA7A2]/10 text-slate-700 hover:text-[#1FA7A2] text-[11px] font-bold border border-slate-200 hover:border-[#1FA7A2]/40 transition-colors">6 أشهر</button>
                                        <button type="button" @click="setYears(1)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#1FA7A2]/10 text-slate-700 hover:text-[#1FA7A2] text-[11px] font-bold border border-slate-200 hover:border-[#1FA7A2]/40 transition-colors">سنة</button>
                                        <button type="button" @click="setYears(2)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#1FA7A2]/10 text-slate-700 hover:text-[#1FA7A2] text-[11px] font-bold border border-slate-200 hover:border-[#1FA7A2]/40 transition-colors">سنتان</button>
                                        <button type="button" @click="setYears(3)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#1FA7A2]/10 text-slate-700 hover:text-[#1FA7A2] text-[11px] font-bold border border-slate-200 hover:border-[#1FA7A2]/40 transition-colors">3 سنوات</button>
                                        <button type="button" @click="setYears(5)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#1FA7A2]/10 text-slate-700 hover:text-[#1FA7A2] text-[11px] font-bold border border-slate-200 hover:border-[#1FA7A2]/40 transition-colors">5 سنوات</button>
                                    </div>

                                    {{-- Stage 3: dir=ltr لمنع تشوّه التاريخ في RTL --}}
                                    <input type="date" dir="ltr"
                                           class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none transition-all text-start disabled:opacity-50 disabled:cursor-not-allowed"
                                           wire:model.defer="expiry_date"
                                           x-bind:disabled="noExpiry">
                                    <p class="text-[11px] text-slate-400 font-medium" x-show="noExpiry" x-cloak>لا ينطبق — لن يُسجَّل تاريخ انتهاء لهذه الوثيقة.</p>
                                    @error('expiry_date') <span class="text-red-500 text-xs font-bold block">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="md:col-span-2 space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('label_file') }}</label>
                                    <label class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 rounded-2xl cursor-pointer bg-slate-50 hover:bg-[#1FA7A2]/5 hover:border-[#1FA7A2]/50 transition-all group overflow-hidden">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6 transition-opacity" wire:loading.class="opacity-50" wire:target="file">
                                            <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center mb-2 group-hover:bg-[#1FA7A2] group-hover:text-white transition-colors text-slate-400">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <p class="text-xs text-slate-500 font-bold">{{ __('file_hint') }}</p>
                                        </div>
                                        
                                        <div wire:loading.flex wire:target="file" class="absolute inset-0 flex-col items-center justify-center bg-white/80 backdrop-blur-sm z-10">
                                            <i class="fas fa-circle-notch fa-spin text-[#1FA7A2] text-2xl mb-2"></i>
                                            <span class="text-xs font-bold text-[#1FA7A2]">{{ __('auth_loading') }}</span>
                                        </div>

                                        <input type="file" class="hidden" wire:model="file" :disabled="uploading" accept=".pdf,.jpg,.jpeg,.png">
                                    </label>
                                    @if($file)
                                        <div class="mt-2 flex items-center gap-2 px-3 py-2 rounded-xl bg-[#1FA7A2]/5 border border-[#1FA7A2]/20">
                                            <i class="fas fa-paperclip text-[#1FA7A2] text-xs shrink-0"></i>
                                            <span class="text-[11px] text-slate-700 font-bold truncate flex-1 min-w-0" title="{{ $file->getClientOriginalName() }}">
                                                {{ \Illuminate\Support\Str::limit($file->getClientOriginalName(), 48, '…') }}
                                            </span>
                                            <button type="button" wire:click="$set('file', null)" :disabled="uploading" class="text-slate-400 hover:text-red-500 transition-colors text-xs disabled:opacity-50 disabled:cursor-not-allowed" title="إزالة الملف">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <p class="text-[11px] text-slate-400 font-medium">{{ __('upload_size_hint', ['max' => 5]) }}</p>
                                    @error('file') <span class="text-red-500 text-xs font-bold block">{{ $message }}</span> @enderror
                                    <span x-show="uploadError" x-text="uploadError" class="text-red-500 text-xs font-bold block"></span>
                                </div>
                            </div>

                            {{-- AMR7 fix: progress bar أثناء temp upload --}}
                            <div x-show="uploading" x-cloak class="mt-6 space-y-1">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-600">
                                    <span><i class="fas fa-circle-notch fa-spin me-1 text-[#1FA7A2]"></i>{{ __('upload_in_progress') }}</span>
                                    <span x-text="progress + '%'"></span>
                                </div>
                                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#1FA7A2] transition-all duration-200" :style="`width: ${progress}%`"></div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                                <button type="button" :disabled="uploading" class="px-6 py-2.5 rounded-xl text-slate-500 font-bold hover:bg-slate-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" @click="if(!uploading) open = false">{{ __('cancel') }}</button>
                                {{-- Hotfix: wire:target بدون comma-separated لمنع closure args mismatch --}}
                                <button type="button" class="px-8 py-2.5 rounded-xl bg-[#1FA7A2] text-white font-bold shadow-lg shadow-[#1FA7A2]/20 hover:bg-[#167F7B] hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                        wire:click="saveDocument"
                                        wire:loading.attr="disabled"
                                        :disabled="uploading">
                                    <span wire:loading.remove wire:target="saveDocument" x-show="!uploading">حفظ ورفع الوثيقة</span>
                                    <span wire:loading wire:target="saveDocument"><i class="fas fa-circle-notch fa-spin"></i> جاري الحفظ</span>
                                    <span x-show="uploading" x-text="progress + '%'" class="text-xs"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>