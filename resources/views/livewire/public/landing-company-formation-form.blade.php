<div class="relative">
    {{-- رسالة النجاح --}}
    @if(session()->has('landing_success'))
        <div class="flex items-start gap-4 bg-emerald-50/80 border border-emerald-100 rounded-2xl p-4 mb-8 shadow-sm animate__animated animate__fadeInDown">
            <div class="w-10 h-10 rounded-xl bg-emerald-100/50 border border-emerald-200 flex items-center justify-center text-emerald-600 shrink-0">
                <i class="fas fa-check-circle text-lg" aria-hidden="true"></i>
            </div>
            <div>
                <h6 class="font-black text-emerald-800 text-sm mb-1">{{ __('request_received_title') }}</h6>
                <p class="text-emerald-600 font-medium text-xs leading-relaxed m-0">{{ __('request_received_msg') }}</p>
            </div>
        </div>
    @endif

    <form wire:submit="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6 relative" novalidate>

        {{-- الاسم --}}
        <div>
            <label for="landing_name" class="block text-xs font-bold text-slate-700 mb-2 px-1">
                {{ __('form_label_name') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative group">
                <input
                    id="landing_name"
                    type="text"
                    wire:model.blur="name"
                    class="w-full h-14 bg-slate-50 border border-slate-200 rounded-xl ps-11 pe-4 text-slate-800 font-bold focus:bg-white focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 hover:border-slate-300 outline-none transition-all placeholder:text-slate-400 placeholder:font-medium @error('name') !border-red-400 !bg-red-50 focus:!ring-red-500/10 @enderror"
                    placeholder="{{ __('form_placeholder_name') }}"
                    autocomplete="name"
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                >
                <i class="fas fa-user absolute top-1/2 -translate-y-1/2 start-4 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none @error('name') !text-red-400 @enderror" aria-hidden="true"></i>
            </div>
            @error('name') <span class="text-red-500 text-xs mt-1.5 block font-bold animate__animated animate__headShake px-1">{{ $message }}</span> @enderror
        </div>

        {{-- الجوال --}}
        <div>
            <label for="landing_phone" class="block text-xs font-bold text-slate-700 mb-2 px-1">
                {{ __('form_label_phone') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative group" dir="ltr">
                <input
                    id="landing_phone"
                    type="tel"
                    wire:model.blur="phone"
                    class="w-full h-14 bg-slate-50 border border-slate-200 rounded-xl pl-11 pr-4 text-slate-800 font-bold text-left focus:bg-white focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 hover:border-slate-300 outline-none transition-all placeholder:text-slate-400 placeholder:font-medium @error('phone') !border-red-400 !bg-red-50 focus:!ring-red-500/10 @enderror"
                    placeholder="05X XXX XXXX"
                    autocomplete="tel"
                    aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                >
                <i class="fas fa-mobile-alt absolute top-1/2 -translate-y-1/2 left-4 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none text-lg @error('phone') !text-red-400 @enderror" aria-hidden="true"></i>
            </div>
            @error('phone') <span class="text-red-500 text-xs mt-1.5 block font-bold text-end animate__animated animate__headShake px-1">{{ $message }}</span> @enderror
        </div>

        {{-- المدينة --}}
        <div>
            <label for="landing_city" class="block text-xs font-bold text-slate-700 mb-2 px-1">
                {{ __('form_label_city') }}
            </label>
            <div class="relative group">
                <input
                    id="landing_city"
                    type="text"
                    wire:model.blur="city"
                    class="w-full h-14 bg-slate-50 border border-slate-200 rounded-xl ps-11 pe-4 text-slate-800 font-bold focus:bg-white focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 hover:border-slate-300 outline-none transition-all placeholder:text-slate-400 placeholder:font-medium @error('city') !border-red-400 !bg-red-50 focus:!ring-red-500/10 @enderror"
                    placeholder="{{ __('form_placeholder_city') }}"
                    autocomplete="address-level2"
                >
                <i class="fas fa-map-marker-alt absolute top-1/2 -translate-y-1/2 start-4 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none @error('city') !text-red-400 @enderror" aria-hidden="true"></i>
            </div>
            @error('city') <span class="text-red-500 text-xs mt-1.5 block font-bold animate__animated animate__headShake px-1">{{ $message }}</span> @enderror
        </div>

        {{-- المرفقات (تصميم محسّن) --}}
        <div>
            <label for="fileUploadLanding" class="block text-xs font-bold text-slate-700 mb-2 px-1">
                {{ __('form_label_attachment') }} <span class="text-slate-400 font-normal">({{ __('Optional') }})</span>
            </label>
            <div class="relative">
                <input type="file" wire:model="attachment" class="hidden" id="fileUploadLanding">
                <label
                    for="fileUploadLanding"
                    class="flex flex-col items-center justify-center w-full h-14 px-4 border-2 border-dashed {{ $attachment ? 'border-[#1FA7A2] bg-[#f0fdfa]' : 'border-slate-200 bg-slate-50 hover:bg-slate-100' }} rounded-xl text-slate-500 cursor-pointer hover:border-[#1FA7A2] transition-all duration-300 group"
                >
                    <span wire:loading wire:target="attachment" class="flex items-center gap-2 text-sm font-bold text-[#1FA7A2]">
                        <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> {{ __('uploading') }}...
                    </span>

                    <span wire:loading.remove wire:target="attachment" class="flex items-center justify-center gap-3 w-full">
                        @if ($attachment)
                            <div class="w-8 h-8 rounded-lg bg-[#1FA7A2]/10 text-[#1FA7A2] flex items-center justify-center shrink-0">
                                <i class="fas fa-file-alt" aria-hidden="true"></i>
                            </div>
                            <span class="text-[#1FA7A2] font-bold text-xs truncate max-w-[150px] md:max-w-[200px]" title="{{ $attachment->getClientOriginalName() }}">
                                {{ $attachment->getClientOriginalName() }}
                            </span>
                            <div class="ms-auto w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                <i class="fas fa-check text-[10px]" aria-hidden="true"></i>
                            </div>
                        @else
                            <i class="fas fa-cloud-upload-alt text-xl text-slate-400 group-hover:text-[#1FA7A2] transition-colors" aria-hidden="true"></i>
                            <span class="text-xs font-bold text-slate-600 group-hover:text-[#1FA7A2] transition-colors">{{ __('choose_file') }}</span>
                        @endif
                    </span>
                </label>
            </div>
            @error('attachment') <span class="text-red-500 text-xs mt-1.5 block font-bold animate__animated animate__headShake px-1">{{ $message }}</span> @enderror
        </div>

        {{-- الملاحظات --}}
        <div class="md:col-span-2">
            <label for="landing_notes" class="block text-xs font-bold text-slate-700 mb-2 px-1">
                {{ __('form_label_notes') }}
            </label>
            <div class="relative group">
                <textarea
                    id="landing_notes"
                    wire:model.blur="notes"
                    rows="4"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-slate-800 font-medium focus:bg-white focus:border-[#1FA7A2] focus:ring-4 focus:ring-[#1FA7A2]/10 hover:border-slate-300 outline-none transition-all placeholder:text-slate-400 resize-none @error('notes') !border-red-400 !bg-red-50 focus:!ring-red-500/10 @enderror"
                    placeholder="{{ __('form_placeholder_notes') }}"
                ></textarea>
            </div>
            @error('notes') <span class="text-red-500 text-xs mt-1.5 block font-bold animate__animated animate__headShake px-1">{{ $message }}</span> @enderror
        </div>

        {{-- الأزرار --}}
        <div class="md:col-span-2 pt-4 flex flex-col sm:flex-row items-center gap-4">
            <button
                type="submit"
                class="w-full sm:w-2/3 h-14 rounded-xl bg-[#25D366] hover:bg-[#128C7E] text-white font-black shadow-lg shadow-[#25D366]/30 hover:shadow-xl hover:shadow-[#25D366]/40 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3 disabled:opacity-75 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">
                    <i class="fab fa-whatsapp text-2xl" aria-hidden="true"></i> {{ __('btn_send_whatsapp') }}
                </span>

                <span wire:loading wire:target="submit" class="flex items-center gap-2">
                    <i class="fas fa-circle-notch fa-spin text-lg" aria-hidden="true"></i> {{ __('sending') }}...
                </span>
            </button>

            <a
                href="https://wa.me/966505336956"
                target="_blank"
                rel="noopener noreferrer"
                class="w-full sm:w-1/3 h-14 rounded-xl border-2 border-slate-200 text-slate-500 font-bold hover:bg-slate-50 hover:text-[#1FA7A2] hover:border-[#1FA7A2]/30 transition-all duration-300 flex items-center justify-center gap-2"
            >
                <i class="far fa-comment-dots text-lg" aria-hidden="true"></i> {{ __('btn_direct_chat') }}
            </a>
        </div>
        
        {{-- غطاء التحميل للفورم بالكامل (Loading Overlay) --}}
        <div wire:loading.flex wire:target="submit" class="absolute inset-0 z-50 bg-white/60 backdrop-blur-sm rounded-xl items-center justify-center" style="display: none;">
            {{-- تركناه فارغاً لأننا نستخدم الـ Spinner في الزر نفسه، هذا فقط لمنع النقر على الحقول أثناء الإرسال --}}
        </div>
    </form>

    @script
    <script>
        $wire.on('open-whatsapp', (event) => {
            const url = event.url || (event[0] && event[0].url);
            if (url) {
                // نستخدم window.open لفتح الرابط في نافذة جديدة مباشرة
                window.open(url, '_blank');
            }
        });
    </script>
    @endscript
</div>