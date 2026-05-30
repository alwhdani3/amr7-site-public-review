<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl border border-slate-100 relative overflow-hidden font-['Tajawal']">
    
    {{-- Background Decoration --}}
    <div class="absolute top-0 right-0 w-32 h-32 bg-[#1FA7A2]/5 rounded-bl-[4rem] -mr-8 -mt-8 pointer-events-none"></div>

    <header class="relative z-10 mb-8">
        <h2 class="text-2xl font-black text-slate-900 mb-3 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shadow-sm">
                <i class="fas fa-shield-alt text-lg"></i>
            </span>
            {{ __('update_password_title') }}
        </h2>
        <p class="text-slate-500 text-sm leading-relaxed max-w-2xl">
            {{ __('update_password_desc') }}
        </p>
    </header>

    <form wire:submit="updatePassword" class="space-y-6 relative z-10">
        
        {{-- Current Password --}}
        <div class="group relative" x-data="{ show: false }">
            <label for="update_password_current_password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                {{ __('current_password') }}
            </label>
            <div class="relative">
                {{-- Icon (Start) --}}
                <div class="absolute inset-y-0 start-0 flex items-center px-4 pointer-events-none text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors">
                    <i class="fas fa-key"></i>
                </div>
                
                {{-- Input --}}
                <input wire:model="current_password" 
                       id="update_password_current_password" 
                       :type="show ? 'text' : 'password'" 
                       class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-4 ps-12 pe-12 font-bold outline-none transition-all shadow-sm placeholder-slate-400" 
                       autocomplete="current-password" 
                       placeholder="••••••••">

                {{-- Toggle (End) --}}
                <button type="button" @click="show = !show" class="absolute inset-y-0 end-0 flex items-center px-4 text-slate-400 hover:text-[#1FA7A2] transition-colors focus:outline-none">
                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('current_password')" class="mt-2 text-red-500 text-xs font-bold" />
        </div>

        {{-- New Password --}}
        <div class="group relative" x-data="{ show: false }">
            <label for="update_password_password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                {{ __('new_password') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center px-4 pointer-events-none text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors">
                    <i class="fas fa-lock"></i>
                </div>
                
                <input wire:model="password" 
                       id="update_password_password" 
                       :type="show ? 'text' : 'password'" 
                       class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-4 ps-12 pe-12 font-bold outline-none transition-all shadow-sm placeholder-slate-400" 
                       autocomplete="new-password" 
                       placeholder="••••••••">

                <button type="button" @click="show = !show" class="absolute inset-y-0 end-0 flex items-center px-4 text-slate-400 hover:text-[#1FA7A2] transition-colors focus:outline-none">
                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-xs font-bold" />
        </div>

        {{-- Confirm Password --}}
        <div class="group relative" x-data="{ show: false }">
            <label for="update_password_password_confirmation" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                {{ __('confirm_password') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center px-4 pointer-events-none text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <input wire:model="password_confirmation" 
                       id="update_password_password_confirmation" 
                       :type="show ? 'text' : 'password'" 
                       class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-4 ps-12 pe-12 font-bold outline-none transition-all shadow-sm placeholder-slate-400" 
                       autocomplete="new-password" 
                       placeholder="••••••••">

                <button type="button" @click="show = !show" class="absolute inset-y-0 end-0 flex items-center px-4 text-slate-400 hover:text-[#1FA7A2] transition-colors focus:outline-none">
                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-500 text-xs font-bold" />
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-4 pt-4">
            <button type="submit" 
                    class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/20 hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-70 flex items-center gap-2" 
                    wire:loading.attr="disabled">
                
                <span wire:loading.remove>{{ __('btn_save') }}</span>
                
                <span wire:loading class="flex items-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i> {{ __('btn_saving') }}
                </span>
            </button>

            <x-action-message class="text-emerald-500 text-sm font-bold animate__animated animate__fadeIn flex items-center gap-1" on="password-updated">
                <i class="fas fa-check-circle"></i> {{ __('msg_saved') }}
            </x-action-message>
        </div>
    </form>
</section>