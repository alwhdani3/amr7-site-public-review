@php
    $isRtl = app()->getLocale() === 'ar';
    $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
@endphp

<div class="max-w-2xl mx-auto font-['Tajawal']" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- Premium Toast Notification (Glassmorphism) --}}
    <div
        x-data="{ show: false, message: '' }"
        x-on:service-request-sent.window="
            show = true;
            message = ($event.detail.message) || ($event.detail[0]?.message) || '{{ __('Request processed successfully') }}';
            setTimeout(() => show = false, 5000);
        "
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-300 cubic-bezier(0.16, 1, 0.3, 1)"
        x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
        class="fixed top-6 start-1/2 -translate-x-1/2 rtl:translate-x-1/2 z-[100] bg-white/90 backdrop-blur-md border border-emerald-100 shadow-[0_10px_40px_rgba(16,185,129,0.15)] rounded-2xl px-5 py-3.5 flex items-center gap-3.5 min-w-[320px]"
        style="display: none;"
        role="status"
        aria-live="polite"
    >
        <div class="bg-emerald-100/80 text-emerald-600 rounded-xl w-10 h-10 flex items-center justify-center shrink-0 shadow-sm border border-emerald-200/50">
            <i class="fas fa-check" aria-hidden="true"></i>
        </div>
        <div>
            <h6 class="mb-0.5 font-black text-slate-800 text-sm">{{ __('Request Sent!') }}</h6>
            <p class="text-slate-500 text-xs font-medium m-0" x-text="message"></p>
        </div>
        <button type="button" @click="show = false" class="ms-auto text-slate-400 hover:text-slate-600 focus:outline-none transition-colors">
            <i class="fas fa-times text-sm" aria-hidden="true"></i>
        </button>
    </div>

    {{-- General error --}}
    @error('general')
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 text-sm font-bold">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit="submit" novalidate class="relative">
        <div class="relative bg-white rounded-[2rem] shadow-xl shadow-slate-200/40 border border-slate-100 p-8 md:p-10 overflow-hidden">

            {{-- Decorative Header --}}
            <div class="absolute top-0 start-0 w-full h-1.5 bg-gradient-to-r from-[#1FA7A2] to-emerald-400" aria-hidden="true"></div>

            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#1FA7A2]/5 text-[#1FA7A2] mb-4">
                    <i class="fas fa-paper-plane text-xl rtl:-scale-x-100" aria-hidden="true"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">
                    {{ __('New Service Request') }}
                </h3>
                <p class="text-slate-500 text-sm font-medium">
                    {{ __('Please fill out the form below and our team will contact you shortly.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Full Name --}}
                <div class="col-span-1">
                    <label for="field_applicant_name" class="block text-sm font-bold text-slate-700 mb-2 px-1">
                        {{ __('Full Name') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <input
                            type="text"
                            id="field_applicant_name"
                            wire:model.blur="applicant_name"
                            placeholder="{{ __('e.g. Ahmed Al-Otaibi') }}"
                            autocomplete="name"
                            class="w-full h-14 bg-slate-50 border rounded-xl ps-11 pe-4 text-slate-800 font-bold focus:bg-white focus:ring-4 transition-all outline-none placeholder:text-slate-400 placeholder:font-medium @error('applicant_name') border-red-300 focus:border-red-500 focus:ring-red-500/10 @else border-slate-200 focus:border-[#1FA7A2] focus:ring-[#1FA7A2]/10 hover:border-slate-300 @enderror"
                            aria-invalid="{{ $errors->has('applicant_name') ? 'true' : 'false' }}"
                        >
                        <i class="fas fa-user absolute top-1/2 -translate-y-1/2 start-4 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none @error('applicant_name') !text-red-400 @enderror" aria-hidden="true"></i>
                    </div>
                    @error('applicant_name')
                        <span role="alert" class="text-red-500 text-xs font-bold mt-1.5 block px-1 animate__animated animate__headShake">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Phone --}}
                <div class="col-span-1">
                    <label for="field_phone" class="block text-sm font-bold text-slate-700 mb-2 px-1">
                        {{ __('Phone Number') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group" dir="ltr">
                        <input
                            type="tel"
                            id="field_phone"
                            wire:model.blur="phone"
                            placeholder="05X XXX XXXX"
                            autocomplete="tel"
                            class="w-full h-14 bg-slate-50 border rounded-xl pl-11 pr-4 text-slate-800 font-bold focus:bg-white focus:ring-4 transition-all outline-none placeholder:text-slate-400 placeholder:font-medium text-left @error('phone') border-red-300 focus:border-red-500 focus:ring-red-500/10 @else border-slate-200 focus:border-[#1FA7A2] focus:ring-[#1FA7A2]/10 hover:border-slate-300 @enderror"
                            aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                        >
                        <i class="fas fa-phone-alt absolute top-1/2 -translate-y-1/2 left-4 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none @error('phone') !text-red-400 @enderror" aria-hidden="true"></i>
                    </div>
                    @error('phone')
                        <span role="alert" class="text-red-500 text-xs font-bold mt-1.5 block px-1 text-end animate__animated animate__headShake">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="field_applicant_email" class="block text-sm font-bold text-slate-700 mb-2 px-1">
                        {{ __('Email Address') }}
                    </label>
                    <div class="relative group" dir="ltr">
                        <input
                            type="email"
                            id="field_applicant_email"
                            wire:model.blur="applicant_email"
                            placeholder="name@company.com"
                            autocomplete="email"
                            class="w-full h-14 bg-slate-50 border rounded-xl pl-11 pr-4 text-slate-800 font-bold focus:bg-white focus:ring-4 transition-all outline-none placeholder:text-slate-400 placeholder:font-medium text-left @error('applicant_email') border-red-300 focus:border-red-500 focus:ring-red-500/10 @else border-slate-200 focus:border-[#1FA7A2] focus:ring-[#1FA7A2]/10 hover:border-slate-300 @enderror"
                        >
                        <i class="fas fa-envelope absolute top-1/2 -translate-y-1/2 left-4 text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors pointer-events-none @error('applicant_email') !text-red-400 @enderror" aria-hidden="true"></i>
                    </div>
                    @error('applicant_email')
                        <span role="alert" class="text-red-500 text-xs font-bold mt-1.5 block px-1 text-end animate__animated animate__headShake">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Service Select / Selected Service --}}
                <div class="col-span-1 md:col-span-2">
                    @if($service)
                        <label class="block text-sm font-bold text-slate-700 mb-2 px-1">
                            {{ __('Service Type') }}
                        </label>

                        <div class="w-full min-h-14 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 flex items-center gap-3">
                            <i class="fas fa-concierge-bell text-[#1FA7A2]" aria-hidden="true"></i>
                            <div class="flex-1">
                                <div class="text-xs text-slate-500 font-semibold mb-0.5">
                                    {{ __('Selected Service') }}
                                </div>
                                <div class="text-slate-800 font-black">
                                    {{ $locale === 'en'
                                        ? ($service->title_en ?? $service->title_ar ?? $service->slug ?? 'Service')
                                        : ($service->title_ar ?? $service->title_en ?? $service->slug ?? 'الخدمة') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <label for="field_service_id" class="block text-sm font-bold text-slate-700 mb-2 px-1">
                            {{ __('Service Type') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <select
                                id="field_service_id"
                                wire:model.change="service_id"
                                class="w-full h-14 bg-slate-50 border rounded-xl ps-11 pe-10 text-slate-800 font-bold focus:bg-white focus:ring-4 transition-all outline-none appearance-none cursor-pointer @error('service_id') border-red-300 focus:border-red-500 focus:ring-red-500/10 @else border-slate-200 focus:border-[#1FA7A2] focus:ring-[#1FA7A2]/10 hover:border-slate-300 @enderror"
                            >
                                <option value="">{{ __('Select Service Type...') }}</option>
                                @foreach(($services ?? collect()) as $item)
                                    @php($title = $locale === 'en' ? ($item->title_en ?? $item->title_ar) : ($item->title_ar ?? $item->title_en))
                                    <option value="{{ $item->id }}">
                                        {{ trim((string) $title) !== '' ? $title : ($item->slug ?? 'Service #'.$item->id) }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fas fa-concierge-bell absolute top-1/2 -translate-y-1/2 start-4 text-[#1FA7A2] transition-colors pointer-events-none @error('service_id') !text-red-400 @enderror" aria-hidden="true"></i>
                            <i class="fas fa-chevron-down absolute top-1/2 -translate-y-1/2 end-4 text-slate-400 text-xs pointer-events-none" aria-hidden="true"></i>
                        </div>
                        @error('service_id')
                            <span role="alert" class="text-red-500 text-xs font-bold mt-1.5 block px-1 animate__animated animate__headShake">
                                {{ $message }}
                            </span>
                        @enderror
                    @endif
                </div>

                {{-- Notes --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="field_notes" class="block text-sm font-bold text-slate-700 mb-2 px-1">
                        {{ __('Request Details') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <textarea
                            id="field_notes"
                            wire:model.blur="notes"
                            rows="4"
                            placeholder="{{ __('Please describe your request in detail...') }}"
                            class="w-full bg-slate-50 border rounded-xl p-4 text-slate-800 font-medium focus:bg-white focus:ring-4 transition-all outline-none resize-none placeholder:text-slate-400 @error('notes') border-red-300 focus:border-red-500 focus:ring-red-500/10 @else border-slate-200 focus:border-[#1FA7A2] focus:ring-[#1FA7A2]/10 hover:border-slate-300 @enderror"
                        ></textarea>
                    </div>
                    @error('notes')
                        <span role="alert" class="text-red-500 text-xs font-bold mt-1.5 block px-1 animate__animated animate__headShake">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Attachment --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="field_attachment" class="block text-sm font-bold text-slate-700 mb-2 px-1">
                        {{ __('Attachment') }}
                    </label>

                    <div class="relative">
                        <label for="field_attachment" class="flex min-h-14 items-center justify-center gap-2 rounded-xl bg-slate-50 border border-dashed border-slate-300 px-4 py-3 text-sm font-black text-[#1FA7A2] hover:bg-[#1FA7A2]/5 cursor-pointer transition-all">
                            <i class="fas fa-paperclip"></i>
                            <span>{{ __('choose_file') }}</span>
                        </label>
                        <input
                            type="file"
                            id="field_attachment"
                            wire:model="attachment"
                            class="sr-only"
                            accept=".pdf,.jpg,.jpeg,.png"
                        >
                    </div>

                    @if($attachment)
                        <div class="mt-3 rounded-2xl bg-white border border-slate-200 shadow-sm p-3 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#1FA7A2]/10 text-[#1FA7A2] flex items-center justify-center shrink-0">
                                <i class="fas fa-file"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-black text-slate-700 truncate">{{ $attachment->getClientOriginalName() }}</p>
                                <p class="text-[11px] text-slate-400 font-medium">
                                    {{ round($attachment->getSize() / 1024, 1) }} KB · {{ $attachment->getMimeType() }}
                                </p>
                            </div>
                            <button type="button"
                                    wire:click="clearAttachment"
                                    class="w-8 h-8 rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-colors"
                                    title="{{ __('remove_file') }}">
                                <i class="fas fa-xmark text-sm"></i>
                            </button>
                        </div>
                    @endif

                    <div wire:loading wire:target="attachment" class="text-xs text-[#1FA7A2] font-bold mt-2">
                        {{ __('sending_file') }}
                    </div>

                    @error('attachment')
                        <span role="alert" class="text-red-500 text-xs font-bold mt-1.5 block px-1 animate__animated animate__headShake">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Terms --}}
                <div class="col-span-1 md:col-span-2 bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                    <label for="field_agreed_terms" class="flex items-start gap-3 text-sm text-slate-600 font-semibold select-none cursor-pointer group">
                        <div class="relative flex items-center justify-center mt-0.5">
                            <input
                                type="checkbox"
                                id="field_agreed_terms"
                                wire:model="agreed_terms"
                                class="peer w-5 h-5 rounded border-slate-300 text-[#1FA7A2] focus:ring-[#1FA7A2]/20 focus:ring-offset-0 transition-colors cursor-pointer accent-[#1FA7A2]"
                            >
                        </div>
                        <span class="leading-snug">
                            {{ __('I agree to the') }}
                            <span class="text-[#1FA7A2]">{{ __('terms and conditions') }}</span>
                            {{ __('and privacy policy.') }}
                        </span>
                    </label>
                    @error('agreed_terms')
                        <span role="alert" class="text-red-500 text-xs font-bold mt-2 block px-1 animate__animated animate__headShake">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div class="col-span-1 md:col-span-2 mt-4">
                    <button
                        type="submit"
                        class="w-full bg-[#1FA7A2] hover:bg-[#167F7B] text-white font-black py-4 rounded-xl shadow-[0_8px_20px_rgba(35,109,111,0.25)] hover:shadow-[0_12px_25px_rgba(35,109,111,0.35)] hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none focus:outline-none focus:ring-4 focus:ring-[#1FA7A2]/20"
                        wire:loading.attr="disabled"
                        wire:target="submit,attachment"
                    >
                        <span wire:loading.remove wire:target="submit,attachment" class="flex items-center gap-2">
                            {{ __('Submit Request') }}
                            <i class="fas fa-arrow-left rtl:-scale-x-100 text-sm" aria-hidden="true"></i>
                        </span>

                        <span wire:loading wire:target="submit,attachment" class="flex items-center gap-2">
                            <i class="fas fa-circle-notch fa-spin text-lg" aria-hidden="true"></i>
                            {{ __('Processing...') }}
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </form>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
