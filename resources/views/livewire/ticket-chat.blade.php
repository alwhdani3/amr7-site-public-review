<div class="space-y-4 font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    <div class="h-[55vh] lg:h-[65vh] overflow-y-auto rounded-3xl border border-slate-200 bg-slate-50 p-4 space-y-4 shadow-inner custom-scrollbar"
         x-data="{ scrollToBottom() { $el.scrollTop = $el.scrollHeight } }"
         x-init="scrollToBottom()"
         x-on:message-sent.window="setTimeout(() => scrollToBottom(), 100)">

        @forelse($replies as $r)
            @php $mine = $r->user_id === auth()->id(); @endphp

            <div class="flex {{ $mine ? 'justify-start' : 'justify-end' }} animate__animated animate__fadeIn">
                <div class="max-w-[85%] lg:max-w-[70%] rounded-2xl px-5 py-4 shadow-sm relative
                            {{ $mine
                                ? 'bg-white border border-slate-100 rounded-tr-none rtl:rounded-tr-2xl rtl:rounded-tl-none text-slate-800'
                                : 'bg-[#1FA7A2] text-white rounded-tl-none rtl:rounded-tl-2xl rtl:rounded-tr-none' }}">

                    <div class="flex justify-between items-center mb-2 gap-4 text-xs opacity-80">
                        <span class="font-bold">
                            {{ $r->user?->name ?? __('system_user') }}
                        </span>
                        <span dir="ltr">{{ $r->created_at?->diffForHumans() }}</span>
                    </div>

                    <div class="text-sm leading-relaxed whitespace-pre-line font-medium">
                        {{ $r->message }}
                    </div>

                    @if(($r->attachments?->count() ?? 0) > 0)
                        <div class="mt-3 pt-3 border-t {{ $mine ? 'border-slate-100' : 'border-white/20' }} flex flex-wrap gap-2">
                            @foreach($r->attachments as $a)
                                <a href="{{ route('attachments.download', $a) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-2 text-xs font-bold hover:underline transition-all {{ $mine ? 'text-[#1FA7A2]' : 'text-white' }}">
                                    <i class="fas fa-paperclip"></i>
                                    <span class="truncate max-w-[150px]">{{ $a->original_name ?? __('attachment') }}</span>
                                    <i class="fas fa-external-link-alt text-[10px]"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="h-full flex flex-col items-center justify-center text-center opacity-40">
                <i class="far fa-comments text-5xl mb-4 text-slate-400"></i>
                <p class="text-slate-500 font-medium">{{ __('no_messages_yet') }}</p>
            </div>
        @endforelse
    </div>

    <div class="relative bg-white rounded-3xl border border-slate-200 p-2 shadow-lg">
        <div wire:loading wire:target="attachment"
             class="absolute inset-0 bg-white/90 z-20 flex items-center justify-center rounded-3xl backdrop-blur-sm">
            <span class="text-[#1FA7A2] text-xs font-bold animate-pulse flex items-center gap-2">
                <i class="fas fa-spinner fa-spin"></i> {{ __('uploading_file') }}
            </span>
        </div>

        <form wire:submit.prevent="send" class="relative">
            <textarea wire:model.defer="message"
                      rows="1"
                      class="w-full bg-transparent border-0 focus:ring-0 text-slate-700 placeholder-slate-400 py-3 px-4 resize-none max-h-32 focus:bg-slate-50 rounded-xl transition-colors custom-scrollbar"
                      placeholder="{{ __('write_reply_placeholder') }}"
                      style="min-height: 50px;"></textarea>

            <div class="flex items-center justify-between px-2 pb-1 pt-2 border-t border-slate-100 mt-1">
                <div class="flex items-center gap-2">
                    <label for="file-upload" class="p-2 rounded-full text-slate-400 hover:text-[#1FA7A2] hover:bg-slate-100 cursor-pointer transition-all" title="{{ __('attach_file') }}">
                        <i class="fas fa-paperclip text-lg"></i>
                        <input type="file" id="file-upload" wire:model="attachment" class="hidden">
                    </label>

                    @if($attachment)
                        <span class="text-xs text-[#1FA7A2] font-bold bg-[#1FA7A2]/10 px-3 py-1 rounded-full animate__animated animate__fadeIn flex items-center gap-1">
                            <i class="fas fa-check-circle"></i> {{ __('file_selected') }}
                        </span>
                    @endif
                </div>

                <button type="submit"
                        class="bg-[#1FA7A2] text-white p-3 rounded-full shadow-md hover:bg-[#167F7B] hover:shadow-lg hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center w-10 h-10"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="fas fa-paper-plane rtl:rotate-180 text-sm"></i>
                    </span>
                    <span wire:loading>
                        <i class="fas fa-spinner fa-spin text-sm"></i>
                    </span>
                </button>
            </div>

            @error('message') <span class="text-red-500 text-xs font-bold px-4 pb-2 block">{{ $message }}</span> @enderror
            @error('attachment') <span class="text-red-500 text-xs font-bold px-4 pb-2 block">{{ $message }}</span> @enderror
        </form>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 99px; }
        .custom-scrollbar::-webkit-scrollbar-track { background-color: transparent; }
    </style>

</div>