<x-layouts.auth>
    <div class="flex flex-col gap-6" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

        <div class="text-center">
            {{-- تم تحويل icon-light و text-teal-amr --}}
            <div class="mx-auto mb-4 w-16 h-16 rounded-2xl flex items-center justify-center bg-teal-50 border border-teal-100 shadow-sm text-[#1FA7A2]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 17v-2"/>
                    <path d="M18 11V9a6 6 0 10-12 0v2"/>
                    <path d="M5 11h14v10H5z"/>
                </svg>
            </div>

            <x-auth.title
                title="{{ __('confirm_password_title') }}"
                description="{{ __('confirm_password_desc') }}"
            />
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-5">
            @csrf

            <div class="space-y-2">
                <flux:input
                    name="password"
                    label="{{ __('auth_label_password') }}"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="{{ __('auth_placeholder_password') }}"
                    viewable
                    class="bg-gray-50 border-gray-200 focus:border-[#1FA7A2] focus:ring-[#1FA7A2]"
                />

                @error('password')
                    {{-- تم تحويل text-danger-amr --}}
                    <div class="text-sm text-red-600 font-semibold">{{ $message }}</div>
                @enderror
            </div>

            {{-- تم تحويل bg-teal-amr-btn و hover --}}
            <flux:button type="submit" class="w-full bg-[#1FA7A2] hover:bg-[#167F7B] text-white rounded-xl py-3 font-bold transition-all shadow-lg shadow-[#1FA7A2]/20">
                {{ __('auth_btn_confirm') }}
            </flux:button>

            <div class="text-center text-sm text-slate-600">
                {{-- تم تحويل الروابط --}}
                <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 hover:underline text-[#1FA7A2] font-bold">
                    {{ __('auth_link_back') }}
                </a>
            </div>
        </form>
    </div>
</x-layouts.auth>