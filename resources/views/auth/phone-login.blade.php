<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth_method_phone_label') }} — {{ config('app.name', 'Amr 7 Business Solutions') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>[x-cloak] { display: none !important; }</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-['Tajawal'] antialiased text-slate-900 bg-white">

<div class="flex flex-col lg:flex-row min-h-screen w-full bg-white font-['Tajawal']"
     x-data="phoneLoginForm()">

    <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 py-12 lg:px-16 xl:px-24 bg-white z-20 relative order-2 lg:order-1">
        <div class="w-full max-w-md mx-auto">

            <div class="mb-10 text-center lg:text-start">
                <a href="/" class="inline-block">
                    <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}"
                         class="h-16 w-auto mb-6 object-contain"
                         alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
                </a>
                <h2 class="text-3xl font-black text-slate-900 mb-2 leading-tight">
                    {{ __('auth_method_phone_label') }}
                </h2>
                <p class="text-slate-500 font-medium text-sm leading-relaxed">
                    {{ __('Please login to continue') }}
                </p>
            </div>

            @unless($enabled)
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-circle-info mt-1"></i>
                        <p class="text-sm font-bold leading-relaxed">
                            {{ __('auth_otp_service_disabled') }}
                        </p>
                    </div>
                    <a href="{{ route('login') }}"
                       class="mt-3 inline-block text-sm font-bold text-[#1FA7A2] hover:underline">
                        {{ __('auth_method_email_label') }} ←
                    </a>
                </div>
            @endunless

            <form @submit.prevent="onSubmit" class="space-y-6"
                  :class="{ 'opacity-60 pointer-events-none': disabled }">

                <div class="group relative" x-show="step === 'phone'">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        {{ __('auth_otp_phone_label') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center px-4 text-slate-400 pointer-events-none rtl:right-auto rtl:left-4 ltr:left-auto ltr:right-4">
                            <i class="fas fa-mobile-screen"></i>
                        </div>
                        <input type="tel"
                               x-model="phone"
                               autocomplete="tel"
                               inputmode="numeric"
                               required
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] block p-4 ps-12 placeholder-slate-400 transition-all font-bold focus:bg-white outline-none shadow-sm"
                               placeholder="05XXXXXXXX">
                    </div>
                </div>

                <div class="group relative" x-show="step === 'code'" x-cloak>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        {{ __('auth_otp_code_label') }}
                    </label>
                    <input type="text"
                           x-model="code"
                           inputmode="numeric"
                           autocomplete="one-time-code"
                           required
                           class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-lg tracking-[0.5em] text-center rounded-xl focus:ring-2 focus:ring-[#1FA7A2]/20 focus:border-[#1FA7A2] block p-4 placeholder-slate-400 transition-all font-black focus:bg-white outline-none shadow-sm"
                           placeholder="••••••">
                    <button type="button"
                            @click="step = 'phone'; code = ''"
                            class="mt-3 text-xs font-bold text-slate-500 hover:text-[#1FA7A2]">
                        ← {{ __('auth_otp_phone_label') }}
                    </button>
                </div>

                <template x-if="message">
                    <div class="rounded-xl border p-3 text-sm font-bold"
                         :class="messageOk
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-red-200 bg-red-50 text-red-700'">
                        <span x-text="message"></span>
                    </div>
                </template>

                <button type="submit"
                        :disabled="loading || disabled"
                        class="w-full py-4 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-black text-lg shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-3 mt-4">
                    <span x-show="!loading"
                          x-text="step === 'phone' ? '{{ __('auth_otp_send_action') }}' : '{{ __('auth_otp_verify_action') }}'"></span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <i class="fas fa-circle-notch fa-spin"></i> {{ __('Processing...') }}
                    </span>
                </button>

                <div class="text-center mt-8">
                    <p class="text-slate-500 text-sm font-medium">
                        <a href="{{ route('login') }}"
                           class="text-[#1FA7A2] font-bold hover:text-[#167F7B] hover:underline transition-colors">
                            {{ __('auth_method_email_label') }}
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-slate-50 to-[#f1f5f9] items-center justify-center relative overflow-hidden sticky top-0 h-screen order-1 lg:order-2">
        <div class="absolute top-0 end-0 w-[500px] h-[500px] bg-[#0A2540]/5 rounded-full blur-3xl -me-32 -mt-32 pointer-events-none"></div>
        <div class="absolute bottom-0 start-0 w-[500px] h-[500px] bg-[#1FA7A2]/10 rounded-full blur-3xl -ms-32 -mb-32 pointer-events-none"></div>

        <div class="relative z-10 p-12 max-w-lg w-full">
            <div class="bg-white border border-slate-200 p-10 rounded-3xl shadow-md text-center">
                <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.svg') }}"
                     class="h-20 mx-auto mb-7 object-contain"
                     alt="{{ app()->getLocale() === 'ar' ? 'شركة آمر سبعة لحلول الأعمال' : 'Amr Seven Business Solutions' }}">
                <h3 class="text-2xl md:text-3xl font-black text-[#0A2540] mb-3 leading-tight">
                    {{ __('auth_brand_slogan_v2') === 'auth_brand_slogan_v2' ? 'بوابتك للسوق السعودي' : __('auth_brand_slogan_v2') }}
                </h3>
                <p class="text-slate-600 font-medium leading-relaxed mb-2 text-sm">
                    {{ __('auth_brand_desc_v2') === 'auth_brand_desc_v2' ? 'أكثر من 15 عامًا في تأسيس الشركات، التراخيص، والامتثال.' : __('auth_brand_desc_v2') }}
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function phoneLoginForm() {
    return {
        step: 'phone',
        phone: '',
        code: '',
        loading: false,
        message: '',
        messageOk: false,
        disabled: @json(! $enabled),

        async onSubmit() {
            if (this.disabled) return;

            this.loading = true;
            this.message = '';

            try {
                const url = this.step === 'phone'
                    ? @json(route('login.phone.send'))
                    : @json(route('login.phone.verify'));

                const payload = this.step === 'phone'
                    ? { phone: this.phone }
                    : { phone: this.phone, code: this.code };

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify(payload)
                });

                if (res.status === 410) {
                    this.disabled = true;
                    const body = await res.json();
                    this.message = body.message || @json(__('auth_otp_service_disabled'));
                    this.messageOk = false;
                    return;
                }

                const data = await res.json();
                this.message = data.message || '';
                this.messageOk = !!data.ok;

                if (data.ok) {
                    if (this.step === 'phone') {
                        this.step = 'code';
                    } else if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }
            } catch (e) {
                this.message = @json(__('auth_otp_service_disabled'));
                this.messageOk = false;
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
</body>
</html>
