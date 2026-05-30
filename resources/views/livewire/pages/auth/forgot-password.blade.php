<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));
            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div class="w-full">
    <div class="text-center mb-10 animate__animated animate__fadeInDown">
        <div class="mx-auto mb-6 w-20 h-20 rounded-[2rem] bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
        </div>

        <h2 class="text-3xl font-black text-slate-900 mb-3">{{ __('forgot_password_title') }}</h2>
        <p class="text-slate-500 font-medium leading-relaxed max-w-sm mx-auto">{{ __('forgot_password_desc') }}</p>
    </div>

    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 text-sm font-bold text-center flex items-center justify-center gap-2 animate__animated animate__pulse">
            <i class="fas fa-check-circle text-lg"></i>
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-6">
        
        <div class="group relative">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('auth_label_email') }}</label>
            <div class="relative">
                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors rtl:right-0 rtl:left-auto ltr:left-auto ltr:right-0">
                    <i class="fas fa-envelope"></i>
                </div>
                
                <input 
                    type="email" 
                    wire:model="email" 
                    class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-4 ps-4 pe-12 font-bold outline-none transition-all shadow-sm placeholder-slate-400 rtl:pr-12 rtl:pl-4 ltr:pl-4 ltr:pr-12"
                    placeholder="name@example.com" 
                    required 
                    autofocus
                >
            </div>
            @error('email') <div class="text-red-500 text-xs font-bold mt-2">{{ $message }}</div> @enderror
        </div>

        <button type="submit" 
                class="w-full py-4 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold text-lg shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/30 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2" 
                wire:loading.attr="disabled">
            <span wire:loading.remove>{{ __('auth_btn_send_reset_link') }}</span>
            <div wire:loading class="flex items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('loading_sending') }}
            </div>
        </button>

        <div class="text-center text-sm font-medium text-slate-500 mt-4">
            <span>{{ __('auth_remember_password') }}</span>
            <a href="{{ route('login') }}" class="text-[#1FA7A2] hover:text-[#167F7B] font-bold ms-1 transition-colors hover:underline" wire:navigate>
                {{ __('auth_link_login') }}
            </a>
        </div>
    </form>
</div>