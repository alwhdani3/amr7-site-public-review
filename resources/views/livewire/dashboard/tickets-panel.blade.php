<div class="font-['Tajawal'] text-slate-800 h-full relative" 
     dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"
     x-data="{ showCreateModal: @entangle('showCreateModal') }">

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
    </style>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 pb-4 border-b border-slate-100 gap-4">
        <h4 class="text-2xl font-black text-slate-900 flex items-center gap-3">
            <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#1FA7A2]/10 to-[#1FA7A2]/20 flex items-center justify-center text-[#1FA7A2] shadow-sm border border-[#1FA7A2]/10">
                <i class="fas fa-headset text-xl"></i>
            </span>
            <div class="flex flex-col">
                <span>{{ __('support_center_title') }}</span>
                <span class="text-xs font-medium text-slate-400 mt-1">{{ __('manage_tickets_subtitle') }}</span>
            </div>
        </h4>

        <button @click="showCreateModal = true" 
                class="group px-6 py-3 rounded-xl bg-[#1FA7A2] text-white font-bold shadow-lg shadow-[#1FA7A2]/20 hover:shadow-xl hover:shadow-[#1FA7A2]/30 hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2">
            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center group-hover:bg-white/30 transition-colors">
                <i class="fas fa-plus text-xs"></i>
            </div>
            {{ __('btn_new_ticket') }}
        </button>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 flex items-center gap-3 shadow-sm animate__animated animate__fadeInDown">
            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check"></i>
            </div>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-220px)] min-h-[600px]">
        
        <div class="lg:col-span-4 h-full flex flex-col">
            <div class="bg-white rounded-[1.5rem] shadow-lg shadow-slate-200/50 border border-slate-100 h-full flex flex-col overflow-hidden">
                <div class="p-5 border-b border-slate-50 bg-slate-50/50 backdrop-blur-sm sticky top-0 z-10">
                    <div class="flex justify-between items-center text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <span class="flex items-center gap-2"><i class="fas fa-list"></i> {{ __('company_tickets_label') }}</span>
                        <span class="bg-slate-200 text-slate-600 px-2 py-0.5 rounded-md">{{ $tickets->count() }}</span>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                    @forelse($tickets as $t)
                        <button type="button"
                                wire:click="openTicket({{ $t->id }})"
                                wire:key="ticket-{{ $t->id }}"
                                class="w-full text-start p-4 rounded-2xl border transition-all duration-200 group relative overflow-hidden
                                {{ $activeTicket && $activeTicket->id === $t->id 
                                    ? 'bg-[#1FA7A2]/5 border-[#1FA7A2] shadow-md ring-1 ring-[#1FA7A2]/20' 
                                    : 'bg-white border-transparent hover:bg-slate-50 hover:border-slate-200' 
                                }}">
                            
                            @if($activeTicket && $activeTicket->id === $t->id)
                                <div class="absolute top-0 start-0 bottom-0 w-1 bg-[#1FA7A2]"></div>
                            @endif

                            <div class="flex justify-between items-start mb-1 ps-2">
                                <span class="font-mono text-[10px] font-bold {{ $activeTicket && $activeTicket->id === $t->id ? 'text-[#1FA7A2]' : 'text-slate-400' }}">
                                    #{{ $t->ticket_number ?? $t->id }}
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold
                                    {{ $t->status == 'open' ? 'bg-emerald-100 text-emerald-600' : 
                                      ($t->status == 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500') }}">
                                    {{ __('status_' . $t->status) }}
                                </span>
                            </div>

                            <div class="font-bold text-slate-800 mb-1 truncate text-sm ps-2 group-hover:text-[#1FA7A2] transition-colors">
                                {{ $t->subject }}
                            </div>

                            <div class="flex items-center justify-between text-xs text-slate-400 ps-2">
                                <span class="flex items-center gap-1">
                                    <i class="far fa-clock text-[10px]"></i> {{ $t->created_at->diffForHumans() }}
                                </span>
                                @if($t->unread_count > 0)
                                    <span class="w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center text-[10px] font-bold shadow-sm animate-pulse">
                                        {{ $t->unread_count }}
                                    </span>
                                @endif
                            </div>
                        </button>
                    @empty
                        <div class="flex flex-col items-center justify-center h-64 text-slate-400">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-inbox text-2xl opacity-40"></i>
                            </div>
                            <p class="text-sm font-bold">{{ __('no_tickets_yet') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:col-span-8 h-full flex flex-col relative">
            <div class="bg-white rounded-[1.5rem] shadow-lg shadow-slate-200/50 border border-slate-100 h-full flex flex-col overflow-hidden relative">
                
                <div wire:loading.flex wire:target="openTicket" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center rounded-[1.5rem]">
                    <div class="flex flex-col items-center gap-3">
                        <i class="fas fa-circle-notch fa-spin text-3xl text-[#1FA7A2]"></i>
                        <span class="text-sm font-bold text-slate-500">{{ __('loading_ticket') }}...</span>
                    </div>
                </div>

                @if(!$activeTicket)
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 bg-slate-50/50">
                        <div class="w-24 h-24 rounded-full bg-white flex items-center justify-center shadow-sm mb-6 border border-slate-100 animate-bounce">
                            <i class="far fa-hand-pointer text-4xl opacity-50 text-[#1FA7A2]"></i>
                        </div>
                        <h3 class="font-black text-xl text-slate-700 mb-2">{{ __('select_ticket_msg') }}</h3>
                        <p class="text-sm text-slate-500">{{ __('select_ticket_desc') }}</p>
                    </div>
                @else
                    <div class="p-6 border-b border-slate-100 bg-white z-20 shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h5 class="text-xl font-black text-slate-900 leading-snug">{{ $activeTicket->subject }}</h5>
                                <div class="flex items-center gap-3 mt-2 text-xs font-bold text-slate-500">
                                    <span class="flex items-center gap-1 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                        <i class="fas fa-hashtag text-[#1FA7A2]"></i> {{ $activeTicket->ticket_number ?? $activeTicket->id }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="far fa-calendar-alt"></i> {{ $activeTicket->created_at->format('Y-m-d') }}
                                    </span>
                                </div>
                            </div>
                            <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-600 shadow-sm flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $activeTicket->priority == 'high' ? 'bg-red-500' : 'bg-blue-500' }}"></span>
                                {{ __('priority_' . $activeTicket->priority) }}
                            </span>
                        </div>

                        <div x-data="{ expanded: false }" class="relative">
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-sm text-slate-700 leading-relaxed shadow-inner"
                                 :class="{ 'line-clamp-2': !expanded }">
                                <span class="font-bold text-[#1FA7A2] block mb-1 text-xs uppercase">{{ __('ticket_desc') }}:</span>
                                {!! nl2br(e($activeTicket->description)) !!}
                            </div>
                            <button @click="expanded = !expanded" class="text-xs font-bold text-[#1FA7A2] mt-1 hover:underline focus:outline-none" x-text="expanded ? '{{ __('show_less') }}' : '{{ __('show_more') }}'"></button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-[#f8fafc] custom-scrollbar scroll-smooth">
                        <div class="flex justify-center">
                            <span class="px-3 py-1 bg-slate-200/50 rounded-full text-[10px] font-bold text-slate-500">
                                {{ __('conversation_started') }} {{ $activeTicket->created_at->format('Y-m-d H:i') }}
                            </span>
                        </div>

                        @forelse($replies as $r)
                            @php $isOwn = $r->user_id == auth()->id(); @endphp
                            <div class="flex w-full {{ $isOwn ? 'justify-start' : 'justify-end' }} group">
                                <div class="max-w-[85%] flex flex-col {{ $isOwn ? 'items-start' : 'items-end' }}">
                                    
                                    <div class="flex items-center gap-2 mb-1.5 px-1 opacity-70 text-[10px]">
                                        <span class="font-bold {{ $isOwn ? 'text-[#1FA7A2]' : 'text-slate-600' }}">
                                            {{ $r->user?->name ?? __('user_unknown') }}
                                        </span>
                                        <span class="text-slate-400">•</span>
                                        <span class="font-mono">{{ $r->created_at->format('H:i A') }}</span>
                                    </div>

                                    <div class="p-4 rounded-2xl text-sm leading-relaxed whitespace-pre-line shadow-sm relative transition-all duration-300
                                                {{ $isOwn 
                                                    ? 'bg-white border border-slate-200 text-slate-700 rtl:rounded-tr-none ltr:rounded-tl-none hover:shadow-md' 
                                                    : 'bg-[#1FA7A2] text-white rtl:rounded-tl-none ltr:rounded-tr-none hover:shadow-md hover:shadow-[#1FA7A2]/20' }}">
                                        {{ $r->message }}

                                        @if($r->attachments->count())
                                            <div class="mt-3 pt-3 border-t {{ $isOwn ? 'border-slate-100' : 'border-white/20' }} flex flex-wrap gap-2">
                                                @foreach($r->attachments as $a)
                                                    <a href="{{ route('attachments.download', $a) }}" target="_blank" 
                                                       class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all
                                                       {{ $isOwn ? 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-100' : 'bg-white/20 text-white hover:bg-white/30 backdrop-blur-sm' }}">
                                                        <i class="fas fa-paperclip me-1.5"></i> {{ $a->original_name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-10 text-slate-400 opacity-60">
                                <i class="far fa-comments text-4xl mb-3"></i>
                                <p class="text-sm font-bold">{{ __('no_replies_yet') }}</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="p-4 bg-white border-t border-slate-100 flex-shrink-0 z-20">
                        <form wire:submit.prevent="sendReply">
                            <div class="relative bg-slate-50 rounded-2xl border border-slate-200 focus-within:border-[#1FA7A2] focus-within:ring-2 focus-within:ring-[#1FA7A2]/10 transition-all p-1">
                                <textarea wire:model.defer="replyMessage" rows="2" 
                                          class="block w-full bg-transparent border-0 text-slate-900 text-sm focus:ring-0 p-3 pe-12 font-medium placeholder-slate-400 resize-none custom-scrollbar"
                                          placeholder="{{ __('write_reply_placeholder') }}"></textarea>
                                
                                <div class="flex justify-between items-center px-2 pb-1 mt-1 border-t border-slate-100 pt-2">
                                    <div class="flex items-center gap-2">
                                        <label class="p-2 text-slate-400 hover:text-[#1FA7A2] hover:bg-slate-200/50 rounded-full cursor-pointer transition-all" title="{{ __('label_files_opt') }}">
                                            <i class="fas fa-paperclip text-lg"></i>
                                            <input type="file" class="hidden" multiple wire:model="replyFiles">
                                        </label>
                                        @if($replyFiles)
                                            <span class="text-xs font-bold text-[#1FA7A2] bg-[#1FA7A2]/10 px-2 py-1 rounded-md animate__animated animate__fadeIn">
                                                {{ count($replyFiles) }} {{ __('files_selected') }}
                                            </span>
                                        @endif
                                    </div>

                                    <button type="submit" 
                                            wire:loading.attr="disabled"
                                            class="px-6 py-2 rounded-xl bg-[#1FA7A2] text-white text-sm font-bold shadow-md hover:bg-[#167F7B] hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="sendReply">{{ __('btn_send_reply') }} <i class="fas fa-paper-plane rtl:rotate-180 ms-1"></i></span>
                                        <span wire:loading wire:target="sendReply"><i class="fas fa-circle-notch fa-spin"></i> {{ __('sending') }}...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        @error('replyMessage') <p class="text-red-500 text-xs font-bold mt-2 ps-1 animate__animated animate__fadeIn">{{ $message }}</p> @enderror
                        @error('replyFiles.*') <p class="text-red-500 text-xs font-bold mt-2 ps-1 animate__animated animate__fadeIn">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div x-show="showCreateModal" 
         style="display: none;"
         class="fixed inset-0 z-[9999] overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true"
         x-on:keydown.escape.window="showCreateModal = false">
        
        <div x-show="showCreateModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="showCreateModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showCreateModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[#1FA7A2]/10 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-pen-fancy text-[#1FA7A2]"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ms-4 sm:text-start w-full">
                            <h3 class="text-lg font-black leading-6 text-slate-900" id="modal-title">{{ __('modal_create_title') }}</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('label_subject') }}</label>
                                    <input type="text" wire:model.defer="subject" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-[#1FA7A2] focus:ring-[#1FA7A2] sm:text-sm py-2.5 font-bold">
                                    @error('subject') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('label_desc') }}</label>
                                    <textarea wire:model.defer="description" rows="4" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-[#1FA7A2] focus:ring-[#1FA7A2] sm:text-sm py-2.5 resize-none font-medium"></textarea>
                                    @error('description') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('label_files_opt') }}</label>
                                    <input type="file" wire:model="newFiles" multiple class="block w-full text-xs text-slate-500 file:me-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#1FA7A2]/10 file:text-[#1FA7A2] hover:file:bg-[#1FA7A2]/20 cursor-pointer bg-slate-50 rounded-xl border border-slate-200">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100 gap-2">
                    <button type="button" 
                            wire:click="createTicket"
                            wire:loading.attr="disabled"
                            class="inline-flex w-full justify-center rounded-xl bg-[#1FA7A2] px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-[#167F7B] sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span wire:loading.remove wire:target="createTicket">{{ __('btn_submit_ticket') }}</span>
                        <span wire:loading wire:target="createTicket"><i class="fas fa-circle-notch fa-spin"></i> {{ __('processing') }}...</span>
                    </button>
                    <button type="button" 
                            @click="showCreateModal = false" 
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">
                        {{ __('cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>