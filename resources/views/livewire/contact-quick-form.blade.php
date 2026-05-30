<?php

use App\Mail\ContactMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    #[Validate('required|string|min:2|max:80')]
    public string $name = '';

    #[Validate('required|string|max:20')]
    public string $phone = '';

    #[Validate('nullable|string|max:2000')]
    public ?string $notes = null;

    /** Honeypot. */
    public string $website = '';

    public function submit(): void
    {
        if (! empty($this->website)) {
            return;
        }

        $this->validate();

        try {
            Mail::to('info@amr-7.sa')->send(new ContactMail([
                'name'  => $this->name,
                'phone' => $this->phone,
                'notes' => $this->notes,
            ]));

            $this->dispatch('notificationsSent', message: 'تم إرسال طلبك بنجاح!');
            $this->reset(['name', 'phone', 'notes']);
        } catch (\Throwable $e) {
            Log::error('Contact Form Error: ' . $e->getMessage());
            $this->addError('phone', 'عذراً، حدث خطأ أثناء الإرسال. يرجى المحاولة لاحقاً.');
        }
    }
}; ?>

<div class="relative bg-white p-6 md:p-10 rounded-[2rem] border border-slate-100 shadow-xl overflow-hidden" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    
    {{-- Loading Overlay --}}
    <div wire:loading.flex wire:target="submit" class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center backdrop-blur-sm">
        <div class="text-center">
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-slate-200 border-t-[#1FA7A2] mb-3"></div>
            <div class="text-xs font-bold text-[#1FA7A2] animate-pulse">{{ __('Sending...') }}</div>
        </div>
    </div>

    {{-- Success Message --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-3 mb-6 p-4 rounded-xl bg-green-50 text-green-700 border border-green-100 animate__animated animate__fadeInDown">
            <i class="fas fa-check-circle text-xl" aria-hidden="true"></i>
            <div class="font-medium text-sm">{{ session('success') }}</div>
        </div>
    @endif

    {{-- Header --}}
    <div class="text-center mb-8">
        <h4 class="text-2xl font-black text-slate-900 mb-2">{{ __('Get in touch') }}</h4>
        <p class="text-slate-500 text-sm font-medium">{{ __('We usually respond within 2 hours') }}</p>
    </div>

    <form wire:submit="submit" novalidate>
        
        {{-- HoneyPot --}}
        <div class="hidden" aria-hidden="true">
            <label for="website_field" class="sr-only">Website</label>
            <input
                id="website_field"
                type="text"
                name="website_field"
                wire:model="website"
                tabindex="-1"
                autocomplete="off"
            >
        </div>

        <div class="space-y-5">
            
            {{-- Name --}}
            <div>
                <label for="contact_quick_name" class="sr-only">{{ __('Your Name') }}</label>
                <div class="group flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 transition-all duration-300 focus-within:bg-white focus-within:border-[#1FA7A2] focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                    <span class="text-slate-400 text-lg group-focus-within:text-[#1FA7A2] transition-colors">
                        <i class="fas fa-user" aria-hidden="true"></i>
                    </span>
                    <input
                        id="contact_quick_name"
                        type="text"
                        class="w-full bg-transparent border-none outline-none text-slate-800 placeholder-slate-400 text-sm px-4 focus:ring-0"
                        wire:model="name"
                        placeholder="{{ __('Your Name') }}"
                        autocomplete="name"
                    >
                </div>
                @error('name') <span class="text-red-500 text-xs font-bold mt-1 block px-2">{{ $message }}</span> @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label for="contact_quick_phone" class="sr-only">{{ __('Phone Number') }}</label>
                <div class="group flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 transition-all duration-300 focus-within:bg-white focus-within:border-[#1FA7A2] focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                    <span class="text-slate-400 text-lg group-focus-within:text-[#1FA7A2] transition-colors">
                        <i class="fas fa-mobile-alt" aria-hidden="true"></i>
                    </span>
                    <input
                        id="contact_quick_phone"
                        type="tel"
                        class="w-full bg-transparent border-none outline-none text-slate-800 placeholder-slate-400 text-sm px-4 focus:ring-0"
                        wire:model="phone"
                        placeholder="{{ __('Phone Number') }}"
                        dir="ltr"
                        autocomplete="tel"
                    >
                </div>
                @error('phone') <span class="text-red-500 text-xs font-bold mt-1 block px-2">{{ $message }}</span> @enderror
            </div>

            {{-- Message --}}
            <div>
                <label for="contact_quick_notes" class="sr-only">{{ __('How can we help you?') }}</label>
                <div class="group flex items-start bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 transition-all duration-300 focus-within:bg-white focus-within:border-[#1FA7A2] focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                    <span class="text-slate-400 text-lg mt-1 group-focus-within:text-[#1FA7A2] transition-colors">
                        <i class="fas fa-comment-dots" aria-hidden="true"></i>
                    </span>
                    <textarea
                        id="contact_quick_notes"
                        class="w-full bg-transparent border-none outline-none text-slate-800 placeholder-slate-400 text-sm px-4 focus:ring-0 resize-none"
                        wire:model="notes"
                        rows="4"
                        placeholder="{{ __('How can we help you?') }}"
                    ></textarea>
                </div>
                @error('notes') <span class="text-red-500 text-xs font-bold mt-1 block px-2">{{ $message }}</span> @enderror
            </div>

            {{-- Submit Button --}}
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full py-3.5 rounded-full bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white font-bold text-sm shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/20 hover:-translate-y-1 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove class="flex items-center justify-center gap-2">
                        {{ __('Send Message') }}
                        <i class="fas fa-paper-plane rtl:rotate-180" aria-hidden="true"></i>
                    </span>
                    <span wire:loading class="flex items-center justify-center gap-2">
                        <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>
                        {{ __('Processing...') }}
                    </span>
                </button>
            </div>

        </div>
    </form>
</div>