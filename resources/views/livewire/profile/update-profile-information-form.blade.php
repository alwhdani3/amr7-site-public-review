<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl border border-slate-100 relative overflow-hidden font-['Tajawal']">
    
    {{-- Background Decoration --}}
    <div class="absolute top-0 right-0 w-32 h-32 bg-[#1FA7A2]/5 rounded-bl-[4rem] -mr-8 -mt-8 pointer-events-none"></div>

    <header class="relative z-10 mb-8">
        <h2 class="text-2xl font-black text-slate-900 mb-3 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shadow-sm">
                <i class="fas fa-id-card text-lg"></i>
            </span>
            {{ __('profile_info_title') }}
        </h2>
        <p class="text-slate-500 text-sm leading-relaxed max-w-2xl">
            {{ __('profile_info_desc') }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="space-y-6 relative z-10">
        
        {{-- Name --}}
        <div class="group relative">
            <label for="name" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                {{ __('name') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center px-4 pointer-events-none text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors">
                    <i class="fas fa-user"></i>
                </div>
                <input wire:model="name" 
                       id="name" 
                       type="text" 
                       class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-4 ps-12 pe-4 font-bold outline-none transition-all shadow-sm placeholder-slate-400" 
                       required 
                       autofocus 
                       autocomplete="name">
            </div>
            <x-input-error class="mt-2 text-red-500 text-xs font-bold" :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div class="group relative">
            <label for="email" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                {{ __('email') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center px-4 pointer-events-none text-slate-400 group-focus-within:text-[#1FA7A2] transition-colors">
                    <i class="fas fa-envelope"></i>
                </div>
                <input wire:model="email" 
                       id="email" 
                       type="email" 
                       class="block w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] p-4 ps-12 pe-4 font-bold outline-none transition-all shadow-sm placeholder-slate-400" 
                       required 
                       autocomplete="username">
            </div>
            <x-input-error class="mt-2 text-red-500 text-xs font-bold" :messages="$errors->get('email')" />

            {{-- Email Verification Section --}}
            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-100">
                    <p class="text-sm font-bold text-amber-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ __('email_unverified') }}
                    </p>

                    <button wire:click.prevent="sendVerification" class="text-xs font-bold text-amber-600 hover:text-amber-800 underline decoration-2 underline-offset-2 transition-colors">
                        {{ __('resend_verification') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-3 font-bold text-emerald-600 text-xs animate__animated animate__fadeIn flex items-center gap-1">
                            <i class="fas fa-check-circle"></i>
                            {{ __('verification_sent') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-4">
            <button type="submit" 
                    class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/20 hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 disabled:opacity-70" 
                    wire:loading.attr="disabled">
                
                <span wire:loading.remove>{{ __('btn_save') }}</span>
                
                <span wire:loading class="flex items-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i> {{ __('btn_saving') }}
                </span>
            </button>

            <x-action-message class="text-emerald-500 text-sm font-bold animate__animated animate__fadeIn flex items-center gap-1" on="profile-updated">
                <i class="fas fa-check-circle"></i> {{ __('msg_saved') }}
            </x-action-message>
        </div>
    </form>
</section>