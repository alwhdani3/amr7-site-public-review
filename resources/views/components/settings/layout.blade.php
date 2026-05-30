<div class="flex flex-col md:flex-row gap-6 md:gap-x-12">
    <aside class="w-full md:w-[260px] shrink-0 md:sticky md:top-6 h-fit">
        <div class="rounded-3xl border border-slate-200 bg-white/90 backdrop-blur-xl shadow-sm shadow-slate-200/40 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center">
                        <span class="text-[#1FA7A2] font-black">A7</span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-slate-900 font-extrabold leading-tight truncate">{{ __('Settings') }}</div>
                        <div class="text-xs text-slate-500 font-medium truncate">{{ __('Manage your account') }}</div>
                    </div>
                </div>
            </div>

            <div class="p-2">
                <flux:navlist class="w-full">
                    @if (Route::has('profile.edit'))
                        <flux:navlist.item
                            :href="route('profile.edit')"
                            :current="request()->routeIs('profile.edit')"
                            wire:navigate
                        >
                            {{ __('Profile') }}
                        </flux:navlist.item>
                    @endif

                    @if (Route::has('user-password.edit'))
                        <flux:navlist.item
                            :href="route('user-password.edit')"
                            :current="request()->routeIs('user-password.edit')"
                            wire:navigate
                        >
                            {{ __('Password') }}
                        </flux:navlist.item>
                    @endif

                    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication() && Route::has('two-factor.show'))
                        <flux:navlist.item
                            :href="route('two-factor.show')"
                            :current="request()->routeIs('two-factor.show')"
                            wire:navigate
                        >
                            {{ __('Two-Factor Auth') }}
                        </flux:navlist.item>
                    @endif

                    @if (Route::has('appearance.edit'))
                        <flux:navlist.item
                            :href="route('appearance.edit')"
                            :current="request()->routeIs('appearance.edit')"
                            wire:navigate
                        >
                            {{ __('Appearance') }}
                        </flux:navlist.item>
                    @endif
                </flux:navlist>
            </div>
        </div>
    </aside>

    <flux:separator class="md:hidden" />

    <div class="flex-1 w-full min-w-0">
        <div class="rounded-3xl border border-slate-200 bg-white/90 backdrop-blur-xl shadow-sm shadow-slate-200/40 overflow-hidden">
            <div class="px-6 sm:px-8 py-6 border-b border-slate-200">
                <div class="flex items-start gap-4">
                    <div class="hidden sm:flex w-11 h-11 rounded-2xl bg-[#1FA7A2]/10 border border-[#1FA7A2]/20 items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-[#1FA7A2]">
                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm1.72 8.28a.75.75 0 0 0-1.06-1.06l-2.25 2.25a.75.75 0 0 0 0 1.06l2.25 2.25a.75.75 0 1 0 1.06-1.06l-1.72-1.72 1.72-1.72Z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <flux:heading size="lg">{{ $heading ?? '' }}</flux:heading>
                        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
                    </div>
                </div>
            </div>

            <div class="px-6 sm:px-8 py-6">
                <div class="w-full max-w-xl space-y-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>