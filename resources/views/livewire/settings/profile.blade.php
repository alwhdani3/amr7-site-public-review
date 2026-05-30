<section class="w-full">

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        @php($user = auth()->user())

        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            {{-- Name --}}
            <div class="space-y-2">
                <label for="profile_name" class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                    {{ __('Name') }}
                </label>

                <input
                    id="profile_name"
                    type="text"
                    wire:model="name"
                    required
                    autofocus
                    autocomplete="name"
                    class="w-full rounded-xl px-4 py-3
                           bg-white dark:bg-slate-900
                           border border-slate-200 dark:border-slate-700
                           text-slate-900 dark:text-white
                           placeholder:text-slate-400
                           outline-none transition
                           focus:border-teal-500/70 focus:ring-2 focus:ring-teal-500/15"
                />

                @error('name')
                    <div class="text-xs font-semibold text-rose-600 dark:text-rose-300">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="space-y-2">
                <label for="profile_email" class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                    {{ __('Email') }}
                </label>

                <input
                    id="profile_email"
                    type="email"
                    wire:model="email"
                    required
                    autocomplete="email"
                    dir="ltr"
                    class="w-full rounded-xl px-4 py-3
                           bg-white dark:bg-slate-900
                           border border-slate-200 dark:border-slate-700
                           text-slate-900 dark:text-white
                           placeholder:text-slate-400
                           outline-none transition
                           focus:border-teal-500/70 focus:ring-2 focus:ring-teal-500/15"
                />

                @error('email')
                    <div class="text-xs font-semibold text-rose-600 dark:text-rose-300">{{ $message }}</div>
                @enderror

                {{-- Verify Email --}}
                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-4 rounded-xl border border-amber-400/30 bg-amber-500/10 px-4 py-3">
                        <p class="text-sm text-amber-800 dark:text-amber-200">
                            {{ __('Your email address is unverified.') }}
                        </p>

                        <button
                            type="button"
                            wire:click.prevent="resendVerificationNotification"
                            class="mt-2 inline-flex items-center text-sm font-extrabold
                                   text-teal-700 dark:text-teal-200
                                   hover:underline"
                        >
                            {{ __('Click here to re-send the verification email.') }}
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-3 text-sm font-extrabold text-emerald-700 dark:text-emerald-300">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap items-center gap-4">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl px-5 py-3 font-extrabold text-white
                           bg-gradient-to-r from-teal-600 to-emerald-600
                           hover:from-teal-500 hover:to-emerald-500
                           transition
                           focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                >
                    {{ __('Save') }}
                </button>

                <x-action-message class="me-3 text-sm font-bold text-emerald-700 dark:text-emerald-300" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
