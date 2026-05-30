<x-layouts.auth>
    <div class="flex flex-col gap-6" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

        <div class="text-center">
            {{-- أيقونة بتصميم موحد --}}
            <div class="mx-auto mb-4 w-16 h-16 rounded-2xl flex items-center justify-center bg-teal-50 border border-teal-100 shadow-sm text-[#1FA7A2] animate__animated animate__fadeIn">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>

            <x-auth-header
                :title="__('verify_email_title')"
                :description="__('verify_email_desc')"
            />
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-center text-sm text-[#1FA7A2] font-bold animate__animated animate__pulse">
                <i class="fas fa-check-circle me-1"></i> {{ __('verify_email_sent') }}
            </div>
        @endif

        <div class="flex flex-col gap-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" class="w-full bg-[#1FA7A2] hover:bg-[#167F7B] text-white rounded-xl py-3 font-bold transition-all shadow-lg shadow-[#1FA7A2]/20 hover:-translate-y-0.5">
                    {{ __('verify_btn_resend') }}
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-sm text-slate-500 hover:text-[#1FA7A2] font-bold transition-colors duration-200 py-2 hover:underline">
                    {{ __('auth_link_logout') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.auth>