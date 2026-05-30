<?php

use App\Models\User;
use App\Models\Company;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $mobile = '';
    public string $type = 'business';
    public string $password = '';
    public string $password_confirmation = '';

    private function normalizeSaudiMobile(string $value): string
    {
        $v = preg_replace('/\s+/', '', trim($value));
        $v = ltrim($v, '+');
        $v = preg_replace('/[^0-9]/', '', $v);

        if (str_starts_with($v, '00966')) {
            $v = '966' . substr($v, 5);
        }

        if (str_starts_with($v, '05')) {
            $v = '966' . substr($v, 1);
        }

        if (preg_match('/^5\d{8}$/', $v)) {
            $v = '966' . $v;
        }

        return $v;
    }

    public function register(): void
    {
        $this->mobile = $this->normalizeSaudiMobile($this->mobile);

        $validated = $this->validate([
            'type' => ['required', 'in:business,individual'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'mobile' => ['required', 'string', 'regex:/^9665\d{8}$/', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['email'] = strtolower($validated['email']);
        $validated['password'] = Hash::make($validated['password']);

        $user = null;

        DB::transaction(function () use ($validated, &$user) {

            $user = User::create([
                'type' => $validated['type'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'password' => $validated['password'],
            ]);

            $companyName = $validated['type'] === 'business'
                ? (__('Business') . ' ' . $validated['name'])
                : (__('Individual') . ' - ' . $validated['name']);

            $company = Company::create([
                'name' => $companyName,
                'city' => 'الرياض',
                'entity_size' => 'unknown',
            ]);

            $company->users()->attach($user->id, [
                'role' => 'owner',
                'is_active' => true,
            ]);

            event(new Registered($user));
            Auth::login($user);
        });

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
@endpush

<div class="min-h-screen w-full flex items-center justify-center px-4 py-10 font-['Tajawal'] bg-slate-900 bg-gradient-to-br from-slate-900 via-[#0a192f] to-[#134e4a]">
    <div class="w-full max-w-xl mx-auto rounded-[2rem] border border-white/10 bg-white/5 backdrop-blur-2xl shadow-2xl p-8 sm:p-12">
        <div class="flex flex-col gap-8">

            <div class="text-center">
                <div class="mx-auto mb-5 w-16 h-16 rounded-2xl flex items-center justify-center bg-teal-400/10 border border-teal-400/20 shadow-[0_0_15px_rgba(45,212,191,0.15)]">
                    <i class="fas fa-rocket text-teal-400 text-2xl"></i>
                </div>

                <h2 class="text-3xl font-black text-white mb-2">
                    {{ __('Create account') }}
                </h2>

                <p class="text-sm font-bold text-teal-100/70">
                    {{ __('Start your journey with us') }}
                </p>
            </div>

            @if ($errors->any())
                <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-5 py-4 text-sm font-bold text-rose-200 flex items-start gap-3">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <div>{{ __('Please check the form fields.') }}</div>
                </div>
            @endif

            <form wire:submit="register" class="space-y-6">

                <div class="space-y-3">
                    <label class="block text-xs font-black tracking-widest text-slate-300 uppercase px-1">
                        {{ __('Account Type') }}
                    </label>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer group">
                            <input type="radio" wire:model.live="type" value="business" class="peer sr-only">
                            <div class="rounded-2xl p-4 text-center transition-all duration-300 border border-white/10 bg-white/5 peer-checked:bg-teal-400/10 peer-checked:border-teal-400/50 peer-checked:shadow-[0_0_20px_rgba(45,212,191,0.15)] hover:bg-white/10 active:scale-95">
                                <i class="fas fa-building text-2xl mb-3 text-slate-400 peer-checked:text-teal-400 transition-colors"></i>
                                <div class="text-sm font-black text-slate-300 peer-checked:text-white transition-colors">
                                    {{ __('Business') }}
                                </div>
                            </div>
                        </label>

                        <label class="cursor-pointer group">
                            <input type="radio" wire:model.live="type" value="individual" class="peer sr-only">
                            <div class="rounded-2xl p-4 text-center transition-all duration-300 border border-white/10 bg-white/5 peer-checked:bg-teal-400/10 peer-checked:border-teal-400/50 peer-checked:shadow-[0_0_20px_rgba(45,212,191,0.15)] hover:bg-white/10 active:scale-95">
                                <i class="fas fa-user text-2xl mb-3 text-slate-400 peer-checked:text-teal-400 transition-colors"></i>
                                <div class="text-sm font-black text-slate-300 peer-checked:text-white transition-colors">
                                    {{ __('Individual') }}
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black tracking-widest text-slate-300 uppercase px-1">
                        {{ __('full_name') }}
                    </label>
                    <div class="relative flex items-center bg-white/5 border border-white/10 rounded-2xl transition-all focus-within:border-teal-400 focus-within:bg-white/10 focus-within:ring-4 focus-within:ring-teal-400/20">
                        <i class="fas fa-signature absolute rtl:right-4 ltr:left-4 text-slate-400"></i>
                        <input name="name" type="text" wire:model="name" required autocomplete="name"
                               placeholder="{{ __('auth_placeholder_name') }}"
                               class="w-full bg-transparent border-none py-3.5 px-12 outline-none text-white font-bold placeholder:text-slate-500 text-sm focus:ring-0" />
                    </div>
                    @error('name') <div class="text-xs font-bold text-rose-400 px-2">{{ $message }}</div> @enderror
                </div>

                <div class="space-y-2" x-data="{ fix(v){ v = (v || '').replace(/\s+/g,''); if(v.startsWith('+')) v = v.slice(1); if(v.startsWith('00966')) v = '966' + v.slice(5); if(v.startsWith('05')) v = '966' + v.slice(1); return v.replace(/[^0-9]/g,''); } }">
                    <label class="block text-xs font-black tracking-widest text-slate-300 uppercase px-1">
                        {{ __('mobile_number') }}
                    </label>
                    <div class="relative flex items-center bg-white/5 border border-white/10 rounded-2xl transition-all focus-within:border-teal-400 focus-within:bg-white/10 focus-within:ring-4 focus-within:ring-teal-400/20">
                        <i class="fas fa-mobile-alt absolute rtl:right-4 ltr:left-4 text-slate-400"></i>
                        <input name="mobile" type="text" wire:model="mobile" required inputmode="tel" dir="ltr"
                               placeholder="{{ __('auth_placeholder_mobile') }}"
                               x-on:input="$event.target.value = fix($event.target.value)"
                               class="w-full bg-transparent border-none py-3.5 px-12 outline-none text-white font-bold placeholder:text-slate-500 text-sm focus:ring-0" />
                    </div>
                    @error('mobile') <div class="text-xs font-bold text-rose-400 px-2">{{ $message }}</div> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black tracking-widest text-slate-300 uppercase px-1">
                        {{ __('email_label') }}
                    </label>
                    <div class="relative flex items-center bg-white/5 border border-white/10 rounded-2xl transition-all focus-within:border-teal-400 focus-within:bg-white/10 focus-within:ring-4 focus-within:ring-teal-400/20">
                        <i class="fas fa-envelope absolute rtl:right-4 ltr:left-4 text-slate-400"></i>
                        <input name="email" type="email" wire:model="email" required autocomplete="username" dir="ltr"
                               placeholder="{{ __('auth_placeholder_email') }}"
                               class="w-full bg-transparent border-none py-3.5 px-12 outline-none text-white font-bold placeholder:text-slate-500 text-sm focus:ring-0" />
                    </div>
                    @error('email') <div class="text-xs font-bold text-rose-400 px-2">{{ $message }}</div> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-2" x-data="{ show: false }">
                        <label class="block text-xs font-black tracking-widest text-slate-300 uppercase px-1">
                            {{ __('auth_label_password') }}
                        </label>
                        <div class="relative flex items-center bg-white/5 border border-white/10 rounded-2xl transition-all focus-within:border-teal-400 focus-within:bg-white/10 focus-within:ring-4 focus-within:ring-teal-400/20">
                            <i class="fas fa-lock absolute rtl:right-4 ltr:left-4 text-slate-400"></i>
                            <input name="password" :type="show ? 'text' : 'password'" wire:model="password" required autocomplete="new-password"
                                   placeholder="••••••••"
                                   class="w-full bg-transparent border-none py-3.5 px-12 outline-none text-white font-bold placeholder:text-slate-500 text-sm focus:ring-0" />
                            <button type="button" @click="show = !show" class="absolute rtl:left-4 ltr:right-4 text-slate-400 hover:text-teal-400 focus:outline-none transition-colors">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2" x-data="{ show: false }">
                        <label class="block text-xs font-black tracking-widest text-slate-300 uppercase px-1">
                            {{ __('auth_label_confirm_password') }}
                        </label>
                        <div class="relative flex items-center bg-white/5 border border-white/10 rounded-2xl transition-all focus-within:border-teal-400 focus-within:bg-white/10 focus-within:ring-4 focus-within:ring-teal-400/20">
                            <i class="fas fa-lock absolute rtl:right-4 ltr:left-4 text-slate-400"></i>
                            <input name="password_confirmation" :type="show ? 'text' : 'password'" wire:model="password_confirmation" required autocomplete="new-password"
                                   placeholder="••••••••"
                                   class="w-full bg-transparent border-none py-3.5 px-12 outline-none text-white font-bold placeholder:text-slate-500 text-sm focus:ring-0" />
                            <button type="button" @click="show = !show" class="absolute rtl:left-4 ltr:right-4 text-slate-400 hover:text-teal-400 focus:outline-none transition-colors">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @error('password') <div class="text-xs font-bold text-rose-400 px-2">{{ $message }}</div> @enderror

                <button type="submit" wire:loading.attr="disabled"
                        class="w-full rounded-2xl px-4 py-4 font-black text-slate-900 transition-all duration-300 bg-gradient-to-r from-teal-400 to-emerald-500 hover:from-teal-300 hover:to-emerald-400 focus:outline-none focus:ring-4 focus:ring-teal-400/30 hover:shadow-[0_10px_25px_rgba(34,199,194,0.3)] active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed group/btn mt-2">
                    <span wire:loading.remove wire:target="register" class="flex items-center justify-center gap-2">
                        {{ __('Register') }}
                        <i class="fas fa-arrow-left transition-transform group-hover/btn:-translate-x-1"></i>
                    </span>
                    <span wire:loading wire:target="register" class="flex items-center justify-center gap-2">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        {{ __('auth_loading_register') }}
                    </span>
                </button>

                <div class="text-center text-sm font-bold text-slate-400 pt-4 border-t border-white/5 mt-6">
                    <span class="me-1">{{ __('Already have an account?') }}</span>
                    <a href="{{ route('login') }}" wire:navigate class="text-teal-400 hover:text-teal-300 hover:underline transition-colors">
                        {{ __('Login') }}
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>