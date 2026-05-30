<div class="relative bg-white rounded-3xl shadow-[0_20px_40px_rgba(0,0,0,0.04)] border border-gray-100 p-6 md:p-10 overflow-hidden">

    {{-- طبقة التحميل --}}
    <div wire:loading wire:target="submit" class="absolute inset-0 z-20 bg-white/60 backdrop-blur-sm flex items-center justify-center rounded-3xl">
        <svg class="animate-spin h-10 w-10 text-[#1FA7A2]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    {{-- رسالة النجاح --}}
    @if(session()->has('success'))
        <div class="mb-8 bg-green-50 border border-green-100 rounded-2xl p-4 flex items-center gap-4 animate__animated animate__fadeIn">
            <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <h5 class="font-bold text-green-800 text-sm mb-0.5">{{ __('request_sent') }}</h5>
                <p class="text-green-600 text-xs mb-0">{{ __('contact_soon') }}</p>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- اسم المنشأة --}}
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('establishment_name') }}</label>
                <div class="relative">
                    <div class="absolute inset-y-0 rtl:right-0 ltr:left-0 flex items-center rtl:pr-4 ltr:pl-4 pointer-events-none text-gray-400">
                        <i class="fas fa-building"></i>
                    </div>
                    <input type="text"
                           wire:model="establishment_name"
                           class="w-full bg-[#f8fafc] border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-[#1FA7A2] focus:border-[#1FA7A2] block rtl:pr-10 ltr:pl-10 p-3.5 transition duration-200 placeholder-gray-400 @error('establishment_name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                           placeholder="{{ __('establishment_placeholder') }}">
                </div>
                @error('establishment_name')
                    <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            {{-- نوع الخدمة --}}
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('service_type') }}</label>
                <div class="relative">
                    <div class="absolute inset-y-0 rtl:right-0 ltr:left-0 flex items-center rtl:pr-4 ltr:pl-4 pointer-events-none text-gray-400">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <select wire:model="service_id"
                            class="w-full bg-[#f8fafc] border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-[#1FA7A2] focus:border-[#1FA7A2] block rtl:pr-10 ltr:pl-10 p-3.5 transition duration-200 appearance-none @error('service_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">{{ __('select_service') }}</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->title_ar ?? $service->title_en ?? $service->slug }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 rtl:left-0 ltr:right-0 flex items-center rtl:pl-4 ltr:pr-4 pointer-events-none text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
                @error('service_id')
                    <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            {{-- رقم الجوال --}}
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('phone') }}</label>
                <div class="relative">
                    <div class="absolute inset-y-0 rtl:right-0 ltr:left-0 flex items-center rtl:pr-4 ltr:pl-4 pointer-events-none text-gray-400">
                        <i class="fas fa-phone"></i>
                    </div>
                    <input type="text"
                           wire:model="phone"
                           class="w-full bg-[#f8fafc] border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-[#1FA7A2] focus:border-[#1FA7A2] block rtl:pr-10 ltr:pl-10 p-3.5 transition duration-200 placeholder-gray-400 @error('phone') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                           dir="ltr"
                           placeholder="05xxxxxxxx">
                </div>
                @error('phone')
                    <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            {{-- رقم السجل التجاري --}}
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('cr_number') }}</label>
                <div class="relative">
                    <div class="absolute inset-y-0 rtl:right-0 ltr:left-0 flex items-center rtl:pr-4 ltr:pl-4 pointer-events-none text-gray-400">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <input type="text"
                           wire:model="cr_number"
                           class="w-full bg-[#f8fafc] border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-[#1FA7A2] focus:border-[#1FA7A2] block rtl:pr-10 ltr:pl-10 p-3.5 transition duration-200 placeholder-gray-400 @error('cr_number') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                           dir="ltr"
                           placeholder="e.g. 1010xxxxxx">
                </div>
                @error('cr_number')
                    <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- المرفقات --}}
        <div>
            <label class="block text-sm font-bold text-gray-900 mb-2">
                {{ __('attachments') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span>
            </label>
            
            <div class="relative">
                <input type="file" wire:model="attachment" id="fileUpload" class="hidden">
                <label for="fileUpload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-2xl cursor-pointer bg-gray-50 hover:bg-white hover:border-[#1FA7A2] transition duration-300">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <i class="fas fa-cloud-upload-alt text-3xl text-[#1FA7A2] mb-3"></i>
                        <p class="mb-1 text-sm text-gray-700 font-bold">{{ __('Click to upload file') }}</p>
                        <p class="text-xs text-gray-500">{{ __('file_requirements_hint', ['types' => 'PDF, JPG, PNG', 'size' => '10MB']) }}</p>
                    </div>
                </label>
            </div>

            <div wire:loading wire:target="attachment" class="text-[#1FA7A2] mt-2 text-xs flex items-center gap-2 font-bold">
                <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                {{ __('uploading') }}
            </div>

            @error('attachment')
                <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- الوصف --}}
        <div>
            <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('description') }}</label>
            <textarea wire:model="description"
                      class="block w-full bg-[#f8fafc] border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-[#1FA7A2] focus:border-[#1FA7A2] p-4 transition duration-200 placeholder-gray-400 resize-none @error('description') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                      rows="4"
                      placeholder="{{ __('description_placeholder') }}"></textarea>
            @error('description')
                <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- زر الإرسال --}}
        <div class="pt-2">
            <button type="submit" 
                    class="w-full bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] hover:to-[#144a4c] text-white font-bold rounded-full py-4 shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition duration-300 flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed" 
                    wire:loading.attr="disabled" 
                    wire:target="submit, attachment">
                <span wire:loading.remove wire:target="submit">
                    {{ __('submit_btn') }} <i class="fas fa-paper-plane text-sm rtl:mr-2 ltr:ml-2"></i>
                </span>
                <span wire:loading wire:target="submit" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    {{ __('processing') }}
                </span>
            </button>
        </div>

    </form>
</div>
