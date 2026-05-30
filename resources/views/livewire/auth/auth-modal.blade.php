<?php

use App\Livewire\Forms\LoginForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public LoginForm $form;

    public bool $open = false;
    public string $mode = 'login';
    public ?string $redirectTo = null;

    public string $reg_name = '';
    public string $reg_email = '';
    public string $reg_phone = '';
    public string $reg_password = '';
    public string $reg_password_confirmation = '';

    #[On('auth-open')]
    public function openModal($payload = []): void
    {
        $payload = is_array($payload) ? $payload : [];

        $mode = $payload['mode'] ?? 'login';
        $this->mode = in_array($mode, ['login', 'register'], true) ? $mode : 'login';
        $this->redirectTo = $payload['redirect'] ?? null;

        $prefill = $payload['prefill'] ?? [];
        if (is_array($prefill)) {
            if (! empty($prefill['email'])) {
                $this->form->email = (string) $prefill['email'];
                $this->reg_email = (string) $prefill['email'];
            }
            if (! empty($prefill['name'])) {
                $this->reg_name = (string) $prefill['name'];
            }
            if (! empty($prefill['mobile'])) {
                $this->reg_phone = (string) $prefill['mobile'];
            }
        }

        $this->open = true;
        $this->resetValidation();
    }

    #[On('auth-close')]
    public function closeModal(): void
    {
        $this->open = false;
        $this->mode = 'login';
        $this->redirectTo = null;

        $this->resetValidation();
        $this->form->reset();

        $this->reset(
            'reg_name',
            'reg_email',
            'reg_phone',
            'reg_password',
            'reg_password_confirmation'
        );
    }

    public function setMode(string $mode): void
    {
        $this->mode = in_array($mode, ['login', 'register'], true) ? $mode : 'login';
        $this->resetValidation();
    }

    public function login(): void
    {
        $this->form->validate();
        $this->form->authenticate();
        request()->session()->regenerate();

        $target = $this->redirectTo ?: session('after_auth_redirect');
        if ($target) {
            session()->forget('after_auth_redirect');
            $this->redirect($target, navigate: false);
            return;
        }

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: false);
    }

    public function register(): void
    {
        $this->validate([
            'reg_name'     => ['required', 'string', 'max:255'],
            'reg_email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'reg_phone'    => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'reg_password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $this->reg_name,
            'email'    => strtolower(trim($this->reg_email)),
            'mobile'   => trim($this->reg_phone),
            'locale'   => app()->getLocale(),
            'password' => Hash::make($this->reg_password),
        ]);

        Auth::login($user);
        request()->session()->regenerate();

        $target = $this->redirectTo ?: session('after_auth_redirect');
        if ($target) {
            session()->forget('after_auth_redirect');
            $this->redirect($target, navigate: false);
            return;
        }

        $this->redirect(route('dashboard', absolute: false), navigate: false);
    }
}; ?>

<div
    x-data="{
        open: @entangle('open').live,
        mode: @entangle('mode').live,
        setMode(m) { $wire.setMode(m); },
    }"
    x-cloak
    @keydown.escape.window="$wire.closeModal()"
    class="relative z-[9999]"
>
    <div
        x-show="open"
        x-transition.opacity.duration.300ms
        class="fixed inset-0 z-[9999] bg-slate-900/70 backdrop-blur-sm"
        @click="$wire.closeModal()"
    ></div>

    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6"
    >
        <div class="relative w-full max-w-5xl max-h-[95vh] overflow-hidden rounded-[2rem] bg-white shadow-2xl border border-white/20 flex flex-col lg:flex-row">

            <button
                type="button"
                @click="$wire.closeModal()"
                aria-label="{{ __('Close') }}"
                class="absolute top-5 right-5 z-50 w-10 h-10 rounded-full bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-500 flex items-center justify-center transition-all rtl:right-auto rtl:left-5 shadow-sm"
            >
                <i class="fas fa-times text-lg" aria-hidden="true"></i>
            </button>

            <div class="w-full lg:w-1/2 flex flex-col justify-center p-8 sm:p-12 bg-white overflow-y-auto custom-scrollbar">
                <div class="lg:hidden text-center mb-8">
                    <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}" width="120" height="48" class="h-12 mx-auto object-contain" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
                </div>

                <div x-show="mode === 'login'">
                    <div class="text-center lg:text-start mb-8">
                        <h2 class="text-3xl font-black text-slate-900 mb-3">{{ __('Welcome Back') }} 👋</h2>
                        <p class="text-slate-500 font-bold text-sm">{{ __('auth_welcome_subtitle') }}</p>
                    </div>

                    <form wire:submit="login" class="space-y-6">
                        <div class="space-y-2">
                            <label for="login_email" class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('email_label') }}</label>
                            <div class="group relative flex items-center bg-slate-50 border-2 border-slate-100 rounded-2xl transition-all focus-within:border-[#1FA7A2] focus-within:bg-white focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                                <i class="fas fa-user absolute rtl:right-4 ltr:left-4 text-slate-300 group-focus-within:text-[#1FA7A2] transition-colors" aria-hidden="true"></i>
                                <input id="login_email" wire:model="form.email" type="text" autocomplete="username"
                                       class="w-full bg-transparent border-none py-4 px-12 outline-none text-slate-800 font-bold placeholder-slate-300 text-sm focus:ring-0"
                                       placeholder="{{ __('auth_login_placeholder') }}" required>
                            </div>
                            @error('form.email') <span class="text-rose-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2" x-data="{ show: false }">
                            <label for="login_password" class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('auth_label_password') }}</label>
                            <div class="group relative flex items-center bg-slate-50 border-2 border-slate-100 rounded-2xl transition-all focus-within:border-[#1FA7A2] focus-within:bg-white focus-within:ring-4 focus-within:ring-[#1FA7A2]/10">
                                <i class="fas fa-lock absolute rtl:right-4 ltr:left-4 text-slate-300 group-focus-within:text-[#1FA7A2] transition-colors" aria-hidden="true"></i>
                                <input id="login_password" wire:model="form.password" :type="show ? 'text' : 'password'" autocomplete="current-password"
                                       class="w-full bg-transparent border-none py-4 px-12 outline-none text-slate-800 font-bold placeholder-slate-300 text-sm focus:ring-0"
                                       placeholder="••••••••" required>
                                <button type="button" @click="show = !show" aria-label="{{ __('Toggle password visibility') }}" class="absolute rtl:left-4 ltr:right-4 text-slate-400 hover:text-[#1FA7A2] focus:outline-none transition-colors">
                                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('form.password') <span class="text-rose-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-between px-1">
                            <label for="login_remember" class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input id="login_remember" wire:model="form.remember" type="checkbox" class="peer w-5 h-5 text-[#1FA7A2] border-slate-300 rounded focus:ring-[#1FA7A2] transition-all">
                                </div>
                                <span class="text-sm font-bold text-slate-500 group-hover:text-slate-700 transition-colors">{{ __('auth_label_remember') }}</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-bold text-[#1FA7A2] hover:underline transition-all">
                                    {{ __('auth_forgot_password') }}
                                </a>
                            @endif
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                                class="w-full py-4 rounded-2xl bg-slate-900 text-white font-black text-base shadow-xl transition-all duration-300 hover:bg-[#1FA7A2] hover:-translate-y-1 active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3 group/btn">
                            <span wire:loading.remove wire:target="login">{{ __('btn_login') }}</span>
                            <span wire:loading wire:target="login">{{ __('auth_loading_login') }}</span>
                            <i class="fas fa-arrow-left group-hover/btn:-translate-x-1 transition-transform" wire:loading.remove wire:target="login" aria-hidden="true"></i>
                        </button>
                    </form>

                    <div class="mt-8 text-center bg-slate-50 py-4 rounded-2xl">
                        <p class="text-slate-500 text-sm font-bold">
                            {{ __('auth_no_account') }}
                            <button type="button" @click="setMode('register')" class="text-[#1FA7A2] font-black hover:underline ms-2">
                                {{ __('Create account') }}
                            </button>
                        </p>
                    </div>
                </div>

                <div x-show="mode === 'register'" style="display:none;">
                    <div class="text-center lg:text-start mb-8">
                        <h2 class="text-3xl font-black text-slate-900 mb-3">{{ __('Create account') }} 🚀</h2>
                        <p class="text-slate-500 font-bold text-sm">{{ __('Start your journey with us') }}</p>
                    </div>

                    <form wire:submit="register" class="space-y-5">
                        <div class="space-y-2">
                            <label for="reg_name" class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('full_name') }}</label>
                            <input id="reg_name" wire:model="reg_name" type="text" autocomplete="name"
                                   class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-3.5 px-5 outline-none text-slate-800 font-bold focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 transition-all text-sm"
                                   placeholder="{{ __('auth_placeholder_name') }}" required>
                            @error('reg_name') <span class="text-rose-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="reg_email" class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('email_label') }}</label>
                            <input id="reg_email" wire:model="reg_email" type="email" autocomplete="email" dir="ltr"
                                   class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-3.5 px-5 outline-none text-slate-800 font-bold focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 transition-all text-sm"
                                   placeholder="{{ __('auth_placeholder_email') }}" required>
                            @error('reg_email') <span class="text-rose-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="reg_phone" class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('mobile_number') }}</label>
                            <input id="reg_phone" wire:model="reg_phone" type="text" autocomplete="tel" dir="ltr"
                                   class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-3.5 px-5 outline-none text-slate-800 font-bold focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 transition-all text-sm"
                                   placeholder="{{ __('auth_placeholder_mobile') }}" required>
                            @error('reg_phone') <span class="text-rose-500 text-xs font-bold px-2">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="space-y-2" x-data="{ show: false }">
                                <label for="reg_password" class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('auth_label_password') }}</label>
                                <div class="relative flex items-center">
                                    <input id="reg_password" wire:model="reg_password" :type="show ? 'text' : 'password'" autocomplete="new-password"
                                           class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-3.5 px-5 rtl:pl-10 ltr:pr-10 outline-none text-slate-800 font-bold focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 transition-all text-sm"
                                           placeholder="••••••••" required>
                                    <button type="button" @click="show = !show" aria-label="{{ __('Toggle password visibility') }}" class="absolute rtl:left-4 ltr:right-4 text-slate-400 hover:text-[#1FA7A2] focus:outline-none transition-colors">
                                        <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="space-y-2" x-data="{ show: false }">
                                <label for="reg_password_confirmation" class="text-xs font-black text-slate-500 uppercase tracking-widest px-1">{{ __('auth_label_confirm_password') }}</label>
                                <div class="relative flex items-center">
                                    <input id="reg_password_confirmation" wire:model="reg_password_confirmation" :type="show ? 'text' : 'password'" autocomplete="new-password"
                                           class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-3.5 px-5 rtl:pl-10 ltr:pr-10 outline-none text-slate-800 font-bold focus:border-[#1FA7A2] focus:bg-white focus:ring-4 focus:ring-[#1FA7A2]/10 transition-all text-sm"
                                           placeholder="••••••••" required>
                                    <button type="button" @click="show = !show" aria-label="{{ __('Toggle password visibility') }}" class="absolute rtl:left-4 ltr:right-4 text-slate-400 hover:text-[#1FA7A2] focus:outline-none transition-colors">
                                        <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @error('reg_password') <span class="text-rose-500 text-xs font-bold block px-2">{{ $message }}</span> @enderror

                        <button type="submit" wire:loading.attr="disabled"
                                class="w-full py-4 rounded-2xl bg-slate-900 text-white font-black text-base shadow-xl transition-all duration-300 hover:bg-[#1FA7A2] hover:-translate-y-1 active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3 mt-4 group/btn">
                            <span wire:loading.remove wire:target="register">{{ __('Register') }}</span>
                            <span wire:loading wire:target="register">{{ __('auth_loading_register') }}</span>
                            <i class="fas fa-arrow-left group-hover/btn:-translate-x-1 transition-transform" wire:loading.remove wire:target="register" aria-hidden="true"></i>
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <p class="text-slate-500 text-sm font-bold">
                            {{ __('Already have an account?') }}
                            <button type="button" @click="setMode('login')" class="text-[#1FA7A2] font-black hover:underline ms-2">
                                {{ __('auth_link_login') }}
                            </button>
                        </p>
                    </div>
                </div>
            </div>

            <div class="hidden lg:flex w-1/2 bg-[#1FA7A2] relative items-center justify-center overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-[#1FA7A2] to-[#134e4a]"></div>

                <div class="relative z-20 text-center text-white max-w-md px-10">
                    <div class="mb-10 inline-block p-6 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/20 shadow-2xl transform transition-transform hover:scale-105">
                        <img src="{{ asset('brand/amr7/amr7-logo-lockup-dark.svg') }}" width="160" height="80" class="h-20 w-auto opacity-100" alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
                    </div>

                    <template x-if="mode === 'login'">
                        <div class="animate__animated animate__fadeIn">
                            <h2 class="text-4xl font-black mb-6 leading-tight">{{ __('Your Gateway to Saudi Market') }}</h2>
                            <p class="text-lg text-teal-50 font-medium leading-relaxed mb-12">
                                {{ __('Over 15 years of experience in Saudi regulations, guiding your success step by step.') }}
                            </p>
                        </div>
                    </template>

                    <template x-if="mode === 'register'">
                        <div class="animate__animated animate__fadeIn">
                            <h2 class="text-4xl font-black mb-6 leading-tight">{{ __('start_your_business_desc') }}</h2>
                            <p class="text-lg text-teal-50 font-medium leading-relaxed mb-12">
                                {{ __('form_subtitle') }}
                            </p>
                        </div>
                    </template>

                    <div class="flex gap-4 justify-center">
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 px-6 py-4 rounded-2xl text-center min-w-[120px] shadow-lg"
                             x-data="{ count: 0, target: 500, start() { this.count = 0; let i = setInterval(() => { this.count += 20; if(this.count >= this.target) { this.count = this.target; clearInterval(i); } }, 40); } }"
                             x-init="$watch('open', value => { if(value) start() }); if(open) start();">
                            <div class="text-3xl font-black text-white mb-1">+<span x-text="count"></span></div>
                            <div class="text-xs uppercase tracking-widest text-teal-100 font-bold">{{ __('stat_companies') }}</div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-md border border-white/20 px-6 py-4 rounded-2xl text-center min-w-[120px] shadow-lg"
                             x-data="{ count: 0, target: 100, start() { this.count = 0; let i = setInterval(() => { this.count += 4; if(this.count >= this.target) { this.count = this.target; clearInterval(i); } }, 40); } }"
                             x-init="$watch('open', value => { if(value) start() }); if(open) start();">
                            <div class="text-3xl font-black text-white mb-1"><span x-text="count"></span>%</div>
                            <div class="text-xs uppercase tracking-widest text-teal-100 font-bold">{{ __('Success') }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
