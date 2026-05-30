<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- Right Column (Chat & Final Files) --}}
    <div class="lg:col-span-8 space-y-8">
        
        {{-- Chat Section --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden flex flex-col h-[600px]">
            {{-- Header --}}
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h5 class="font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-comments text-[#1FA7A2]"></i>
                    {{ __('section_conversation') }}
                </h5>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#1FA7A2]/10 text-[#1FA7A2]">
                    {{ $statusLabels[$req->status] ?? $req->status }}
                </span>
            </div>

            {{-- Messages Area --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/30 custom-scrollbar">
                @forelse($messages as $m)
                    <div class="flex w-full {{ $m->user_id === auth()->id() ? 'justify-start' : 'justify-end' }}">
                        <div class="max-w-[85%]">
                            <div class="flex items-center gap-2 mb-1 px-1 {{ $m->user_id === auth()->id() ? 'flex-row' : 'flex-row-reverse' }}">
                                <span class="text-xs font-bold {{ $m->user_id === auth()->id() ? 'text-[#1FA7A2]' : 'text-slate-500' }}">
                                    {{ $m->user?->name ?? '—' }}
                                    @if($m->user_id === auth()->id()) ({{ __('you') }}) @endif
                                </span>
                                <span class="text-[10px] text-slate-400">{{ $m->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                            
                            <div class="p-4 rounded-2xl text-sm leading-relaxed whitespace-pre-wrap shadow-sm
                                        {{ $m->user_id === auth()->id() 
                                            ? 'bg-white border border-slate-100 text-slate-700 rounded-tl-none' 
                                            : 'bg-[#1FA7A2] text-white rounded-tr-none' }}">
                                {{ $m->body }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-slate-400">
                        <i class="far fa-comment-dots text-3xl mb-2 opacity-50"></i>
                        <p class="text-sm font-medium">{{ __('no_messages_yet') }}</p>
                    </div>
                @endforelse
            </div>

            {{-- Input Area --}}
            <div class="p-4 bg-white border-t border-slate-100">
                <label class="block text-xs font-bold text-slate-400 mb-2">{{ __('label_write_message') }}</label>
                <div class="flex gap-3">
                    <textarea class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-3 outline-none transition-all shadow-inner resize-none" 
                              rows="2" 
                              wire:model.defer="message" 
                              placeholder="{{ __('placeholder_message') }}"></textarea>
                    
                    <button class="shrink-0 w-12 h-auto rounded-xl bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center"
                            wire:click="sendMessage" 
                            wire:loading.attr="disabled">
                        <i class="fas fa-paper-plane rtl:rotate-180" wire:loading.remove></i>
                        <svg wire:loading class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
                @error('message') <div class="text-red-500 text-xs font-bold mt-2">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Final Files Section --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8">
            <h5 class="font-bold text-slate-900 mb-6 flex items-center gap-2 border-b border-slate-100 pb-4">
                <i class="fas fa-file-check text-[#1FA7A2]"></i>
                {{ __('section_final_files') }}
            </h5>

            <div class="grid gap-3">
                @forelse($finalFiles as $f)
                    <a href="{{ route('financial-statements.file.download', $f->id)}}" class="flex items-center justify-between p-4 rounded-xl bg-[#1FA7A2]/5 border border-[#1FA7A2]/10 hover:bg-[#1FA7A2] hover:text-white group transition-all duration-300">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-[#1FA7A2] shadow-sm group-hover:text-[#1FA7A2]">
                                <i class="fas fa-file-pdf text-lg"></i>
                            </div>
                            <span class="font-bold text-sm">{{ $f->original_name ?? __('default_final_file') }}</span>
                        </div>
                        <i class="fas fa-download opacity-50 group-hover:opacity-100"></i>
                    </a>
                @empty
                    <div class="text-center py-8 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50">
                        <p class="text-slate-400 font-medium text-sm">{{ __('no_final_files') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Left Column (Notes & Uploads) --}}
    <div class="lg:col-span-4 space-y-8">
        
        {{-- General Notes --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8">
            <h5 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fas fa-sticky-note text-amber-500"></i>
                {{ __('section_general_notes') }}
            </h5>
            
            <textarea class="w-full bg-amber-50/50 border border-amber-100 text-slate-700 text-sm rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 p-4 outline-none transition-all shadow-inner resize-none mb-4 placeholder-slate-400" 
                      rows="4" 
                      wire:model.defer="client_notes"
                      placeholder="{{ __('placeholder_notes') }}"></textarea>
            @error('client_notes') <div class="text-red-500 text-xs font-bold mb-3">{{ $message }}</div> @enderror
            
            <button class="w-full py-3 rounded-xl border-2 border-[#1FA7A2] text-[#1FA7A2] font-bold hover:bg-[#1FA7A2] hover:text-white transition-all duration-300"
                    wire:click="saveNotes" 
                    wire:loading.attr="disabled">
                {{ __('btn_save_notes') }}
            </button>
        </div>

        {{-- Upload Extra Files --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8">
            <h5 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                <i class="fas fa-cloud-upload-alt text-[#1FA7A2]"></i>
                {{ __('section_upload_extra') }}
            </h5>
            <p class="text-slate-400 text-xs font-medium mb-6">{{ __('upload_extra_help') }}</p>

            <div class="space-y-4">
                @foreach($kinds as $key => $label)
                    @php $isReq = in_array($key, $required, true); @endphp
                    <div class="group">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-slate-600 group-hover:text-[#1FA7A2] transition-colors">{{ $label }}</label>
                            @if($isReq)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-500 border border-red-100">{{ __('badge_required') }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">{{ __('badge_optional') }}</span>
                            @endif
                        </div>
                        
                        <input type="file" 
                               class="block w-full text-xs text-slate-500
                                      file:mr-0 file:ml-2 file:py-2 file:px-3
                                      file:rounded-lg file:border-0
                                      file:text-xs file:font-bold
                                      file:bg-[#1FA7A2]/10 file:text-[#1FA7A2]
                                      hover:file:bg-[#1FA7A2]/20
                                      cursor-pointer border border-slate-200 rounded-lg bg-slate-50 h-10 pt-1.5 px-2 focus:outline-none focus:border-[#1FA7A2]"
                               wire:model="extraFiles.{{ $key }}" 
                               multiple
                               accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png,.webp">
                        
                        @error("extraFiles.$key.*") <div class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</div> @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-6 pt-6 border-t border-slate-100">
                <button class="w-full py-3 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/20 hover:-translate-y-0.5 transition-all duration-300"
                        wire:click="uploadExtra" 
                        wire:loading.attr="disabled">
                    {{ __('btn_upload_files') }}
                </button>
                <p class="text-slate-400 text-[10px] text-center mt-3">{{ __('file_upload_help') }}</p>
            </div>
        </div>

        {{-- Previous Files --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8">
            <h5 class="font-bold text-slate-900 mb-6 flex items-center gap-2">
                <i class="fas fa-history text-slate-400"></i>
                {{ __('section_previous_files') }}
            </h5>

            <div class="space-y-2">
                @forelse($clientFiles as $f)
                    <a href="{{ route('fs.file.download', $f->id) }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:text-[#1FA7A2] group-hover:bg-[#1FA7A2]/10 transition-colors">
                            <i class="fas fa-paperclip text-xs"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-600 truncate group-hover:text-slate-900">{{ $f->original_name ?? __('default_file_name') }}</span>
                    </a>
                @empty
                    <div class="text-center text-slate-400 text-sm py-4">{{ __('no_previous_files') }}</div>
                @endforelse
            </div>
        </div>

    </div>
</div>