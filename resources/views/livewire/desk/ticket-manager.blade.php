<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-140px)] min-h-[600px]" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    <div class="lg:col-span-1 flex flex-col bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
        
        <div class="p-4 border-b border-slate-100 bg-white z-10">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center px-4 pointer-events-none text-slate-400 rtl:right-auto rtl:left-4 ltr:left-auto ltr:right-4">
                    <i class="fas fa-search"></i>
                </div>
                <input wire:model.live.debounce.300ms="filters.search" 
                       type="text" 
                       placeholder="{{ __('Search tickets...') }}" 
                       class="w-full bg-slate-50 border-none text-slate-700 text-sm rounded-xl py-3.5 px-4 ps-12 focus:ring-2 focus:ring-[#1FA7A2]/20 focus:bg-white transition-all">
            </div>
        </div>

        <div class="flex-grow overflow-y-auto custom-scrollbar p-3 space-y-2">
            @forelse($tickets as $ticket)
                <button wire:click="selectTicket({{ $ticket->id }})" 
                        class="w-full text-start group p-4 rounded-2xl border transition-all duration-200 relative overflow-hidden
                               {{ $selectedTicket && $selectedTicket->id === $ticket->id 
                                  ? 'bg-[#1FA7A2]/5 border-[#1FA7A2] shadow-sm' 
                                  : 'bg-white border-transparent hover:bg-slate-50 hover:border-slate-200' }}">
                    
                    @if($selectedTicket && $selectedTicket->id === $ticket->id)
                        <div class="absolute inset-y-0 ltr:left-0 rtl:right-0 w-1 bg-[#1FA7A2]"></div>
                    @endif

                    <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-slate-800 text-sm truncate w-3/4 group-hover:text-[#1FA7A2] transition-colors">
                            {{ $ticket->subject }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium">
                            {{ $ticket->created_at->diffForHumans(null, true) }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center mt-2">
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-[10px]">
                                <i class="fas fa-user text-slate-400"></i>
                            </div>
                            <span class="truncate max-w-[100px]">{{ $ticket->user->name ?? __('Unknown User') }}</span>
                        </div>
                        
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border 
                            {{ $ticket->status === 'open' ? 'bg-green-50 text-green-600 border-green-100' : '' }}
                            {{ $ticket->status === 'closed' ? 'bg-slate-100 text-slate-500 border-slate-200' : '' }}
                            {{ $ticket->status === 'pending' ? 'bg-yellow-50 text-yellow-600 border-yellow-100' : '' }}">
                            {{ __('status_' . $ticket->status) }}
                        </span>
                    </div>
                </button>
            @empty
                <div class="flex flex-col items-center justify-center h-64 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-search text-slate-300 text-2xl"></i>
                    </div>
                    <p class="text-slate-400 text-sm font-medium">{{ __('No tickets found') }}</p>
                </div>
            @endforelse
        </div>

        <div class="p-3 border-t border-slate-100 bg-slate-50/50">
            {{ $tickets->links(data: ['scrollTo' => false]) }}
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full relative">
        
        @if($selectedTicket)
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-start z-20">
                <div>
                    <h2 class="text-xl font-black text-slate-900 mb-1 flex items-center gap-2">
                        {{ $selectedTicket->subject }}
                        <span class="text-xs font-normal text-slate-400 font-mono bg-white border border-slate-200 px-2 py-0.5 rounded-md">
                            #{{ $selectedTicket->ticket_number ?? $selectedTicket->id }}
                        </span>
                    </h2>
                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <span class="flex items-center gap-1"><i class="fas fa-building text-slate-400"></i> {{ $selectedTicket->department->name ?? '-' }}</span>
                        <span class="flex items-center gap-1"><i class="far fa-clock text-slate-400"></i> {{ $selectedTicket->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
                
                @if($selectedTicket->status !== 'closed')
                    <button wire:click="close({{ $selectedTicket->id }})" 
                            wire:confirm="{{ __('Are you sure you want to close this ticket?') }}"
                            class="px-4 py-2 rounded-full border border-slate-200 text-slate-500 text-xs font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-100 transition-all flex items-center gap-2">
                        <i class="fas fa-check"></i> {{ __('Close Ticket') }}
                    </button>
                @endif
            </div>

            <div class="flex-grow overflow-y-auto custom-scrollbar p-6 bg-slate-50 space-y-6" id="chat-container">
                
                <div class="flex justify-center">
                    <div class="bg-white border border-yellow-100 rounded-2xl p-5 shadow-sm max-w-3xl w-full relative overflow-hidden">
                        <div class="absolute top-0 ltr:left-0 rtl:right-0 w-1 h-full bg-yellow-400"></div>
                        <h6 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                            <i class="fas fa-info-circle text-yellow-400"></i> {{ __('Problem Description') }}
                        </h6>
                        <div class="text-slate-700 text-sm leading-relaxed whitespace-pre-line">
                            {!! nl2br(e($selectedTicket->description ?? $selectedTicket->message)) !!}
                        </div>
                        
                        @if($selectedTicket->attachments->count())
                            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap gap-2">
                                @foreach($selectedTicket->attachments as $att)
                                    <a href="{{ route('attachments.download', $att) }}" target="_blank" 
                                       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600 hover:text-[#1FA7A2] hover:border-[#1FA7A2] transition-all">
                                        <i class="fas fa-paperclip text-slate-400"></i> {{ $att->original_name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink-0 mx-4 text-slate-300 text-xs font-bold">{{ __('Replies History') }}</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <div class="space-y-4">
                    @foreach($selectedTicket->replies as $reply)
                        @php $isMe = $reply->user_id === auth()->id(); @endphp
                        
                        <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} animate__animated animate__fadeIn">
                            <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[80%]">
                                
                                <div class="px-5 py-3.5 shadow-sm relative text-sm
                                            {{ $isMe 
                                                ? 'bg-[#1FA7A2] text-white rounded-2xl rounded-tr-none rtl:rounded-tr-2xl rtl:rounded-tl-none' 
                                                : 'bg-white border border-slate-200 text-slate-700 rounded-2xl rounded-tl-none rtl:rounded-tl-2xl rtl:rounded-tr-none' }}">
                                    
                                    <div class="whitespace-pre-wrap">{{ $reply->message }}</div>

                                    @if($reply->attachments && $reply->attachments->count())
                                        <div class="mt-2 pt-2 border-t {{ $isMe ? 'border-white/20' : 'border-slate-100' }}">
                                            @foreach($reply->attachments as $att)
                                                <a href="{{ route('attachments.download', $att) }}" target="_blank" class="block text-xs underline opacity-80 hover:opacity-100 truncate">
                                                    <i class="fas fa-paperclip me-1"></i> {{ $att->original_name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 mt-1 px-1">
                                    <span class="text-[10px] text-slate-400 font-bold">{{ $reply->user->name ?? __('System User') }}</span>
                                    <span class="text-[10px] text-slate-300">•</span>
                                    <span class="text-[10px] text-slate-300">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="h-20"></div>
            </div>

            <div class="absolute bottom-0 left-0 right-0 bg-white p-4 border-t border-slate-100 z-30">
                <form wire:submit="reply">
                    <div class="relative bg-slate-50 border border-slate-200 rounded-2xl flex items-end p-2 focus-within:ring-2 focus-within:ring-[#1FA7A2]/20 focus-within:border-[#1FA7A2] transition-all shadow-sm">
                        
                        <textarea wire:model="replyMessage" 
                                  rows="1" 
                                  class="w-full bg-transparent border-0 focus:ring-0 text-slate-800 placeholder-slate-400 text-sm py-3 px-4 resize-none max-h-32 custom-scrollbar" 
                                  placeholder="{{ __('Write a reply...') }}"></textarea>

                        <div class="flex items-center gap-2 pb-1 pe-2">
                            
                            <div class="relative">
                                <input type="file" wire:model="attachments" multiple id="file-upload" class="hidden">
                                <label for="file-upload" class="p-2 rounded-full text-slate-400 hover:text-[#1FA7A2] hover:bg-slate-200 cursor-pointer transition-colors relative" title="{{ __('Attach File') }}">
                                    <i class="fas fa-paperclip"></i>
                                    @if($attachments)
                                        <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                                    @endif
                                </label>
                            </div>

                            <button type="submit" 
                                    class="p-2.5 rounded-xl bg-[#1FA7A2] text-white hover:bg-[#167F7B] disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md flex items-center justify-center"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove><i class="fas fa-paper-plane rtl:rotate-180"></i></span>
                                <span wire:loading><i class="fas fa-spinner fa-spin"></i></span>
                            </button>
                        </div>
                    </div>
                    @error('replyMessage') <span class="text-red-500 text-xs mt-1 block px-2">{{ $message }}</span> @enderror
                    @error('attachments.*') <span class="text-red-500 text-xs mt-1 block px-2">{{ $message }}</span> @enderror
                </form>
            </div>

        @else
            <div class="flex flex-col items-center justify-center h-full text-center p-8 bg-slate-50/30">
                <div class="w-32 h-32 bg-slate-100 rounded-full flex items-center justify-center mb-6 animate-pulse">
                    <i class="far fa-comments text-5xl text-slate-300"></i>
                </div>
                <h5 class="text-2xl font-black text-slate-800 mb-2">{{ __('Select a Ticket') }}</h5>
                <p class="text-slate-500 max-w-xs mx-auto">{{ __('Please select a ticket from the list to view details and reply.') }}</p>
            </div>
        @endif
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 99px; }
    .custom-scrollbar::-webkit-scrollbar-track { background-color: transparent; }
</style>

@script
<script>
    $wire.on('update-browser-title', (event) => {
        document.title = event.title;
    });
</script>
@endscript