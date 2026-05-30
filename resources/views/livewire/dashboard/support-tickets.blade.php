<div class="animate__animated animate__fadeIn" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-5 w-full md:w-auto">
                <div class="w-14 h-14 rounded-2xl bg-[#f0fdfa] flex items-center justify-center text-[#1FA7A2] shadow-sm ring-4 ring-[#f0fdfa]">
                    <i class="fas fa-headset text-2xl"></i>
                </div>
                <div>
                    <h5 class="text-xl font-black text-slate-800">{{ __('support_center_title') }}</h5>
                    <div class="flex gap-3 mt-1 text-xs font-bold text-slate-500">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> {{ __('open_tickets') }}: {{ $this->openCount }}</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ __('today_tickets') }}: {{ $this->todayCount }}</span>
                    </div>
                </div>
            </div>

            <button type="button" 
                    wire:click="$set('showCreateTicket', true)"
                    class="w-full md:w-auto px-6 py-3 rounded-xl bg-[#1FA7A2] text-white font-bold shadow-lg shadow-[#1FA7A2]/20 hover:bg-[#167F7B] hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> {{ __('btn_new_ticket') }}
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-220px)] min-h-[600px]">

        <div class="lg:col-span-4 flex flex-col h-full bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/80 backdrop-blur-sm sticky top-0 z-10">
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('search_ticket_placeholder') }}" class="w-full bg-white border border-slate-200 rounded-xl rtl:pl-4 rtl:pr-10 ltr:pr-4 ltr:pl-10 py-2.5 text-sm font-bold focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/10 outline-none transition-all">
                    <div class="absolute rtl:right-3 ltr:left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
            
            <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                @forelse($tickets as $t)
                    @php
                        $isActive = $activeTicket && $activeTicket->id === $t->id;
                        $sla = $t->sla_deadline ? \Carbon\Carbon::parse($t->sla_deadline) : null;
                    @endphp

                    <button type="button"
                            wire:click="openTicket({{ $t->id }})"
                            class="w-full text-start p-4 rounded-2xl border transition-all duration-200 group relative
                            {{ $isActive 
                                ? 'bg-[#f0fdfa] border-[#1FA7A2] shadow-md z-10' 
                                : 'bg-white border-transparent hover:bg-slate-50 hover:border-slate-200' 
                            }}">
                        
                        <div class="flex justify-between items-start mb-1.5">
                            <span class="font-mono text-[11px] font-bold {{ $isActive ? 'text-[#1FA7A2]' : 'text-slate-400' }}">#{{ $t->ticket_number }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $t->status == 'open' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ __('status_' . $t->status) }}
                            </span>
                        </div>

                        <div class="font-bold text-slate-800 text-sm mb-1 truncate">
                            {{ $t->subject }}
                        </div>

                        <div class="flex items-center justify-between mt-2">
                             <div class="flex items-center gap-1 text-[10px] text-slate-500 font-bold">
                                <i class="fas fa-user-circle text-slate-300"></i>
                                <span class="truncate max-w-[100px]">{{ $t->company?->name }}</span>
                            </div>
                            @if($sla)
                                <span class="text-[10px] font-mono {{ $sla->isPast() ? 'text-red-500' : 'text-emerald-500' }}">
                                    <i class="fas fa-clock"></i> {{ $sla->diffForHumans(null, true, true) }}
                                </span>
                            @endif
                        </div>
                    </button>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                        <i class="far fa-folder-open text-3xl mb-2 opacity-50"></i>
                        <span class="text-xs font-bold">{{ __('no_tickets_found') }}</span>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-8 h-full relative">
            
            <div wire:loading.flex wire:target="openTicket" class="absolute inset-0 z-20 bg-white/80 backdrop-blur-sm rounded-3xl flex items-center justify-center">
                <div class="flex flex-col items-center gap-3">
                    <i class="fas fa-circle-notch fa-spin text-3xl text-[#1FA7A2]"></i>
                    <span class="text-sm font-bold text-slate-600">{{ __('loading_ticket') }}</span>
                </div>
            </div>

            @if($activeTicket)
                <div class="flex flex-col h-full bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden"
                     x-data="{ 
                        scrollBottom() { 
                            if (this.$refs.chatContainer) {
                                this.$refs.chatContainer.scrollTo({ top: this.$refs.chatContainer.scrollHeight, behavior: 'smooth' });
                            }
                        }
                     }"
                     x-init="scrollBottom(); $wire.on('ticket-updated', () => setTimeout(() => scrollBottom(), 100))">
                    
                    <div class="px-6 py-4 border-b border-slate-100 bg-white flex justify-between items-start shadow-sm z-10">
                        <div>
                            <h3 class="text-lg font-black text-slate-800 mb-1 flex items-center gap-2">
                                {{ $activeTicket->subject }}
                                <span class="text-xs font-normal text-slate-400 font-mono">#{{ $activeTicket->ticket_number }}</span>
                            </h3>
                            <div class="flex items-center gap-3 text-xs font-bold text-slate-500">
                                <span class="flex items-center gap-1"><i class="fas fa-building text-slate-300"></i> {{ $activeTicket->company?->name }}</span>
                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                <span class="text-[#1FA7A2]">{{ $activeTicket->department?->name ?? __('general_department') }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                             <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-50 border border-slate-100 text-slate-600">
                                {{ __('priority_' . $activeTicket->priority) }}
                            </span>
                        </div>
                    </div>

                    <div x-ref="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-6 bg-[#f8fafc] custom-scrollbar scroll-smooth">
                        
                        <div class="flex justify-start w-full">
                            <div class="max-w-[85%] bg-white border border-slate-200/60 p-5 rounded-2xl rtl:rounded-tr-none ltr:rounded-tl-none shadow-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-bold text-[#1FA7A2]">{{ $activeTicket->user?->name ?? __('customer_label') }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $activeTicket->created_at->format('Y-m-d H:i') }}</span>
                                </div>
                                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                                    {{ $activeTicket->description }}
                                </div>
                                @if($activeTicket->attachments->count())
                                    <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap gap-2">
                                        @foreach($activeTicket->attachments as $a)
                                            <a href="{{ route('attachments.download', $a) }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 hover:bg-slate-100 border border-slate-100 text-xs font-bold text-slate-600 transition-colors">
                                                <i class="fas fa-file text-[#1FA7A2]"></i> {{ $a->original_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        @foreach($activeTicket->replies as $r)
                            @php $isOwn = $r->user_id == auth()->id(); @endphp
                            <div class="flex w-full {{ $isOwn ? 'justify-start' : 'justify-end' }}">
                                <div class="max-w-[85%] group">
                                    <div class="flex items-center gap-2 mb-1 px-1 {{ $isOwn ? '' : 'flex-row-reverse' }}">
                                        <span class="text-[10px] font-bold {{ $isOwn ? 'text-[#1FA7A2]' : 'text-slate-500' }}">
                                            {{ $r->user?->name }}
                                        </span>
                                        <span class="text-[10px] text-slate-300 font-mono">{{ $r->created_at->format('H:i') }}</span>
                                    </div>
                                    <div class="p-4 rounded-2xl text-sm leading-relaxed whitespace-pre-line shadow-sm relative transition-all
                                         {{ $isOwn 
                                            ? 'bg-white border border-slate-200 text-slate-700 rtl:rounded-tr-none ltr:rounded-tl-none' 
                                            : 'bg-[#1FA7A2] text-white rtl:rounded-tl-none ltr:rounded-tr-none shadow-md shadow-[#1FA7A2]/10' }}">
                                        {{ $r->message }}
                                        
                                        @php $atts = $r->attachments ?? collect(); @endphp
                                        @if($atts->count())
                                            <div class="mt-3 pt-3 border-t {{ $isOwn ? 'border-slate-100' : 'border-white/20' }} flex flex-wrap gap-2">
                                                @foreach($atts as $ra)
                                                    <a href="{{ route('attachments.download', $ra) }}" target="_blank" class="inline-flex items-center px-2 py-1 rounded text-xs {{ $isOwn ? 'bg-slate-50 text-slate-600' : 'bg-white/20 text-white hover:bg-white/30' }}">
                                                        <i class="fas fa-paperclip me-1"></i> {{ $ra->original_name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="h-4"></div>
                    </div>

                    <div class="p-4 bg-white border-t border-slate-100 z-10">
                        <form wire:submit="sendReply" class="relative">
                            
                            @if($replyFiles)
                                <div class="flex gap-2 mb-3 overflow-x-auto pb-1">
                                    @foreach($replyFiles as $file)
                                        <div class="flex items-center gap-2 px-3 py-1 rounded-lg bg-[#f0fdfa] border border-[#1FA7A2]/20 text-xs font-bold text-[#1FA7A2]">
                                            <i class="fas fa-file"></i> {{ $file->getClientOriginalName() }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex gap-3 items-end bg-slate-50 p-2 rounded-2xl border border-slate-200 focus-within:border-[#1FA7A2] focus-within:ring-2 focus-within:ring-[#1FA7A2]/10 transition-all">
                                <button type="button" onclick="document.getElementById('fileInput').click()" class="p-3 text-slate-400 hover:text-[#1FA7A2] hover:bg-slate-200 rounded-xl transition-colors shrink-0">
                                    <i class="fas fa-paperclip text-lg"></i>
                                </button>
                                <input type="file" id="fileInput" class="hidden" multiple wire:model="replyFiles">

                                <textarea wire:model="replyMessage" 
                                          rows="1" 
                                          class="w-full bg-transparent border-none focus:ring-0 p-3 text-slate-800 font-medium placeholder-slate-400 resize-none max-h-32 custom-scrollbar" 
                                          placeholder="{{ __('write_reply_placeholder') }}"
                                          oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>

                                <button type="submit" 
                                        class="p-3 bg-[#1FA7A2] text-white rounded-xl shadow-lg shadow-[#1FA7A2]/20 hover:shadow-[#1FA7A2]/40 hover:scale-105 transition-all disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
                                        wire:loading.attr="disabled">
                                    <i class="fas fa-paper-plane rtl:rotate-180" wire:loading.remove></i>
                                    <i class="fas fa-circle-notch fa-spin" wire:loading></i>
                                </button>
                            </div>
                            @error('replyMessage') <span class="text-red-500 text-xs font-bold mt-1 block px-2">{{ $message }}</span> @enderror
                        </form>
                    </div>
                </div>
            @else
                <div class="h-full flex flex-col items-center justify-center bg-white rounded-3xl border border-slate-100 text-slate-400 shadow-sm">
                    <div class="w-32 h-32 bg-slate-50 rounded-full flex items-center justify-center mb-6 animate-pulse">
                        <i class="far fa-hand-pointer text-5xl opacity-20 text-[#1FA7A2]"></i>
                    </div>
                    <h3 class="font-black text-xl text-slate-700 mb-2">{{ __('no_ticket_selected') }}</h3>
                    <p class="text-sm font-medium text-slate-500">{{ __('select_ticket_hint') }}</p>
                </div>
            @endif
        </div>
    </div>

    <div x-data="{ 
            open: @entangle('showCreateTicket'),
            close() { this.open = false }
         }" 
         x-cloak 
         class="relative z-[9999]"
         x-on:keydown.escape.window="close()">
        
        <div x-show="open" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" x-transition.opacity @click="close()"></div>

        <div x-show="open" class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="open"
                     x-trap.noscroll="open"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                     class="relative w-full max-w-xl bg-white rounded-[2rem] shadow-2xl overflow-hidden border border-slate-100">

                    <div class="bg-slate-50/80 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-black text-slate-800">{{ __('btn_new_ticket') }}</h3>
                            <p class="text-slate-500 text-xs font-bold mt-1">{{ __('ticket_creation_subtitle') }}</p>
                        </div>
                        <button @click="close()" class="w-8 h-8 rounded-full bg-white text-slate-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-all border border-slate-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-8">
                        <form wire:submit="createTicket" class="space-y-5">
                            <div class="space-y-1">
                                <label class="text-sm font-bold text-slate-700">{{ __('ticket_subject') }} <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute rtl:right-4 ltr:left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1FA7A2]">
                                        <i class="fas fa-heading"></i>
                                    </div>
                                    <input type="text" wire:model.blur="subject" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl rtl:pr-10 rtl:pl-4 ltr:pl-10 ltr:pr-4 py-3 text-slate-900 font-bold outline-none transition-all placeholder-slate-400">
                                </div>
                                @error('subject') <span class="text-red-500 text-xs font-bold block">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-sm font-bold text-slate-700">{{ __('department') }}</label>
                                    <div class="relative">
                                        <select wire:model="department_id" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] rounded-xl px-4 py-3 text-slate-900 font-bold outline-none appearance-none cursor-pointer">
                                            <option value="">{{ __('select_department') }}</option>
                                            @foreach($departments as $d) <option value="{{ $d->id }}">{{ $d->name }}</option> @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down absolute rtl:left-4 ltr:right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-sm font-bold text-slate-700">{{ __('priority') }}</label>
                                    <div class="relative">
                                        <select wire:model="priority" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] rounded-xl px-4 py-3 text-slate-900 font-bold outline-none appearance-none cursor-pointer">
                                            <option value="low">{{ __('priority_low') }}</option>
                                            <option value="medium">{{ __('priority_medium') }}</option>
                                            <option value="high">{{ __('priority_high') }}</option>
                                        </select>
                                        <i class="fas fa-chevron-down absolute rtl:left-4 ltr:right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-bold text-slate-700">{{ __('ticket_details') }} <span class="text-red-500">*</span></label>
                                <textarea wire:model.blur="description" rows="4" class="w-full bg-slate-50 border border-slate-200 focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 rounded-xl p-4 text-slate-900 font-medium outline-none transition-all placeholder-slate-400 resize-none"></textarea>
                                @error('description') <span class="text-red-500 text-xs font-bold block">{{ $message }}</span> @enderror
                            </div>

                            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                                <button type="button" @click="close()" class="px-6 py-3 rounded-xl text-slate-500 font-bold hover:bg-slate-50">{{ __('btn_cancel') }}</button>
                                <button type="submit" class="px-8 py-3 rounded-xl bg-[#1FA7A2] text-white font-bold shadow-lg shadow-[#1FA7A2]/20 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center gap-2" wire:loading.attr="disabled">
                                    <span wire:loading.remove>{{ __('btn_submit_ticket') }}</span>
                                    <span wire:loading><i class="fas fa-circle-notch fa-spin"></i></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>