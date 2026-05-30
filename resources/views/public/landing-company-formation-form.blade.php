<div>
    
    {{-- رسالة النجاح --}}
    @if(session()->has('landing_success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl p-4 mb-6 shadow-sm animate__animated animate__fadeIn">
            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 shrink-0">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <h6 class="font-bold text-green-700 text-sm">{{ __('request_received_title') }}</h6>
                <p class="text-green-600/80 text-xs">{{ __('request_received_msg') }}</p>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- الاسم --}}
        <div>
            <label for="landing_name" class="block text-xs font-bold text-slate-700 mb-1.5 ms-1">{{ __('form_label_name') }}</label>
            <div class="relative group">
                <input type="text" id="landing_name" wire:model="name"
                       class="w-full h-12 bg-slate-50 border border-slate-200 rounded-xl px-4 rtl:pr-10 ltr:pl-10 text-slate-800 focus:bg-white focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/10 outline-none transition-all placeholder:text-slate-400"
                       placeholder="{{ __('form_placeholder_name') }}">
                <i class="fas fa-user absolute top-1/2 -translate-y-1/2 rtl:right-4 ltr:left-4 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none"></i>
            </div>
            @error('name') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
        </div>

        {{-- الجوال --}}
        <div>
            <label for="landing_phone" class="block text-xs font-bold text-slate-700 mb-1.5 ms-1">{{ __('form_label_phone') }}</label>
            <div class="relative group">
                <input type="tel" id="landing_phone" wire:model="phone"
                       class="w-full h-12 bg-slate-50 border border-slate-200 rounded-xl px-4 rtl:pr-10 ltr:pl-10 text-slate-800 font-mono text-end focus:bg-white focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/10 outline-none transition-all placeholder:text-slate-400"
                       dir="ltr"
                       placeholder="05xxxxxxxx">
                <i class="fas fa-mobile-alt absolute top-1/2 -translate-y-1/2 rtl:right-4 ltr:left-4 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none"></i>
            </div>
            @error('phone') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
        </div>

        {{-- المدينة --}}
        <div>
            <label for="landing_city" class="block text-xs font-bold text-slate-700 mb-1.5 ms-1">{{ __('form_label_city') }}</label>
            <div class="relative group">
                <input type="text" id="landing_city" wire:model="city"
                       class="w-full h-12 bg-slate-50 border border-slate-200 rounded-xl px-4 rtl:pr-10 ltr:pl-10 text-slate-800 focus:bg-white focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/10 outline-none transition-all placeholder:text-slate-400"
                       placeholder="{{ __('form_placeholder_city') }}">
                <i class="fas fa-map-marker-alt absolute top-1/2 -translate-y-1/2 rtl:right-4 ltr:left-4 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none"></i>
            </div>
            @error('city') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
        </div>

        {{-- المرفقات --}}
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5 ms-1">{{ __('form_label_attachment') }}</label>
            <div class="relative">
                <input type="file" wire:model="attachment" class="hidden" id="fileUploadLanding">
                <label for="fileUploadLanding"
                       class="flex items-center justify-center w-full h-12 px-4 border border-dashed border-slate-300 rounded-xl bg-slate-50 text-slate-500 cursor-pointer hover:bg-[#f0fdfa] hover:border-[#1FA7A2] hover:text-[#1FA7A2] transition-all duration-300">
                    <span wire:loading.remove wire:target="attachment" class="flex items-center gap-2 text-xs font-bold">
                        <i class="fas fa-paperclip"></i> {{ __('choose_file') }}
                    </span>
                    <span wire:loading wire:target="attachment" class="flex items-center gap-2 text-xs font-bold text-[#1FA7A2]">
                        <i class="fas fa-spinner fa-spin"></i> {{ __('uploading') }}...
                    </span>
                </label>
            </div>
            @error('attachment') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
        </div>

        {{-- الملاحظات --}}
        <div class="md:col-span-2">
            <label for="landing_notes" class="block text-xs font-bold text-slate-700 mb-1.5 ms-1">{{ __('form_label_notes') }}</label>
            <textarea id="landing_notes" wire:model="notes" rows="4"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-slate-800 focus:bg-white focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/10 outline-none transition-all placeholder:text-slate-400 resize-none"
                      placeholder="{{ __('form_placeholder_notes') }}"></textarea>
            @error('notes') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
        </div>

        {{-- الأزرار --}}
        <div class="md:col-span-2 pt-2 space-y-3">
            <button type="submit"
                    class="w-full py-3.5 rounded-full bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">
                    <i class="fab fa-whatsapp text-lg"></i> {{ __('btn_send_whatsapp') }}
                </span>
                <span wire:loading wire:target="submit" class="flex items-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i> {{ __('sending') }}
                </span>
            </button>

            <a href="https://wa.me/966505336956" target="_blank" rel="noopener noreferrer"
               class="w-full py-3 rounded-full border border-slate-300 text-slate-500 font-bold hover:bg-slate-50 hover:text-[#1FA7A2] hover:border-[#1FA7A2] transition-all duration-300 flex items-center justify-center gap-2">
                <i class="far fa-comment-dots text-lg"></i> {{ __('btn_direct_chat') }}
            </a>
        </div>

    </form>

    {{-- Script to handle redirect --}}
    <script>
        document.addEventListener('livewire:init', () => {
             Livewire.on('open-whatsapp', (event) => {
                 const url = event.url || (event[0] && event[0].url) || event.detail.url;
                 if(url) window.open(url, '_blank');
             });
        });
    </script>

</div>
