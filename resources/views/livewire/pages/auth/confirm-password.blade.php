<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $password = '';

    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('validation.current_password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full">
    <div class="text-center mb-10 animate__animated animate__fadeInDown">
        <div class="mx-auto mb-6 w-20 h-20 rounded-[2rem] bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <h2 class="text-3xl font-black text-slate-900 mb-3">{{ __('confirm_password_title') }}</h2>
        <p class="text-slate-500 font-medium leading-relaxed max-w-sm mx-auto">{{ __('confirm_password_desc') }}</p>
    </div>

    <form wire:submit="confirmPassword" class="space-y-6">
        
        <div x-data="{ show: false }" class="group relative">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('auth_label_password') }}</label>
            <div class="relative">
                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors rtl:right-0 rtl:left-auto ltr:left-auto ltr:right-0">
                    <i class="fas fa-lock"></i>
                </div>
                
                <input 
                    :type="show ? 'text' : 'password'"
                    wire:model="password"
                    class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-4 ps-4 pe-12 font-bold outline-none transition-all shadow-sm placeholder-slate-400 rtl:pr-12 rtl:pl-4 ltr:pl-4 ltr:pr-12"
                    placeholder="••••••••"
                    required 
                    autocomplete="current-password"
                >

                <button type="button" 
                        @click="show = !show" 
                        class="absolute inset-y-0 left-0 flex items-center px-4 text-slate-400 hover:text-[#1FA7A2] transition-colors focus:outline-none rtl:left-0 rtl:right-auto ltr:right-auto ltr:left-0">
                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            @error('password') <div class="text-red-500 text-xs font-bold mt-2">{{ $message }}</div> @enderror
        </div>

        <button type="submit" 
                class="w-full py-4 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold text-lg shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/30 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2" 
                wire:loading.attr="disabled">
            <span wire:loading.remove>{{ __('btn_confirm_password') }}</span>
            <div wire:loading class="flex items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('loading_processing') }}
            </div>
        </button>

        <div class="text-center">
            <a href="{{ url()->previous() }}" class="text-sm font-bold text-slate-400 hover:text-[#1FA7A2] transition-colors">
                {{ __('btn_cancel') }}
            </a>
        </div>
    </form>
</div>