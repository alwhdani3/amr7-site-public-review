<div class="min-h-screen bg-slate-50 font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    {{-- 
        ملاحظة هامة:
        هذا هو الـ Root Element الوحيد (div).
        تم إزالة @extends و @section لأن هذا مكون Livewire وليس صفحة كاملة.
    --}}

    {{-- Hero Section --}}
    <section class="relative pt-24 pb-20 overflow-hidden bg-gradient-to-b from-white to-slate-100">
        <div class="absolute inset-0 opacity-40 pointer-events-none" 
             style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 40px 40px;">
        </div>
        
        <div class="absolute top-[-50%] left-1/2 -translate-x-1/2 w-[600px] h-[600px] rounded-full bg-[#44BDB8]/15 blur-[100px] pointer-events-none"></div>

        <div class="container mx-auto px-4 relative z-10 text-center">
            <div class="mb-6 animate__animated animate__fadeInDown">
                <span class="inline-flex items-center px-6 py-2 rounded-full bg-[#1FA7A2]/10 text-[#1FA7A2] font-bold border border-[#1FA7A2]/20 shadow-sm">
                    <i class="fas fa-shield-alt mx-2 rtl:order-last"></i> {{ __('secure_payment_channels') }}
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-slate-900 mb-6 animate__animated animate__fadeInUp">
                {{ __('bank_accounts_title_1') }} 
                <span class="text-[#1FA7A2]">{{ __('bank_accounts_title_2') }}</span>
            </h1>
            
            <p class="text-lg text-slate-500 max-w-3xl mx-auto leading-loose animate__animated animate__fadeInUp delay-100">
                {{ __('bank_accounts_intro') }}
            </p>
        </div>
    </section>

    {{-- Banks Grid --}}
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 justify-center">
                @forelse($banks as $bank)
                    <div class="group relative bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-[#1FA7A2]/10 hover:border-[#1FA7A2] flex flex-col h-full animate__animated animate__fadeInUp">
                        
                        {{-- Header: Logo & Watermark --}}
                        <div class="flex justify-between items-start mb-8">
                            <div class="w-20 h-20 rounded-2xl bg-white border border-slate-100 p-2 shadow-sm flex items-center justify-center">
                                @php
                                    $bankLogoFallback = asset('images/default-bank.png');
                                    $finalLogo = null;
                                    $cleanName = str_replace(['أ', 'إ', 'آ'], 'ا', $bank->bank_name);
                                    $upperName = strtoupper($cleanName);

                                    if (!empty($bank->bank_logo)) {
                                        $finalLogo = $bank->bank_logo;
                                    } else {
                                        if (str_contains($cleanName, 'الراجحي')) $finalLogo = 'rajhi.png';
                                        elseif (str_contains($cleanName, 'الاهلي') || str_contains($upperName, 'SNB')) $finalLogo = 'snb.svg';
                                    }

                                    $bankLogoUrl = $finalLogo ? asset('assets/banks/' . $finalLogo) : $bankLogoFallback;
                                @endphp
                                <img src="{{ $bankLogoUrl }}" class="max-w-full max-h-full object-contain"
                                     onerror="this.onerror=null;this.src='{{ $bankLogoFallback }}'" alt="{{ $bank->bank_name }}">
                            </div>
                            <img src="{{ asset('brand/amr7/amr7-mark-light.svg') }}" class="h-6 opacity-30 grayscale" alt="" aria-hidden="true">
                        </div>

                        {{-- Bank Name --}}
                        <div class="text-end mb-6">
                            <h4 class="text-xl font-bold text-slate-900">{{ $bank->bank_name }}</h4>
                        </div>

                        {{-- Account Details (Copyable) --}}
                        <div class="space-y-4 flex-grow">
                            
                            {{-- Account Number --}}
                            <div x-data="{ copied: false }">
                                <span class="text-xs text-slate-400 font-bold mb-1 block">{{ __('account_number') }}</span>
                                <div class="flex justify-between items-center p-4 rounded-xl bg-slate-50 border border-slate-100 group-hover:border-[#1FA7A2]/30 transition-colors">
                                    <span class="text-slate-800 font-bold font-mono tracking-wider" dir="ltr">{{ $bank->account_number }}</span>
                                    <button @click="navigator.clipboard.writeText('{{ $bank->account_number }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                                            class="text-[#1FA7A2] hover:text-[#167F7B] hover:scale-110 transition-transform focus:outline-none"
                                            title="{{ __('copy') }}">
                                        <i class="fas" :class="copied ? 'fa-check text-green-500' : 'fa-copy'"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- IBAN --}}
                            <div x-data="{ copied: false }">
                                <span class="text-xs text-slate-400 font-bold mb-1 block">{{ __('iban_number') }}</span>
                                <div class="flex justify-between items-center p-4 rounded-xl bg-slate-50 border border-slate-100 group-hover:border-[#1FA7A2]/30 transition-colors">
                                    <span class="text-[#1FA7A2] font-bold font-mono text-sm break-all" dir="ltr">{{ $bank->iban }}</span>
                                    <button @click="navigator.clipboard.writeText('{{ $bank->iban }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                                            class="text-[#1FA7A2] hover:text-[#167F7B] hover:scale-110 transition-transform focus:outline-none ms-2"
                                            title="{{ __('copy') }}">
                                        <i class="fas" :class="copied ? 'fa-check text-green-500' : 'fa-copy'"></i>
                                    </button>
                                </div>
                            </div>

                        </div>

                        {{-- Beneficiary Name --}}
                        <div class="mt-6 p-4 rounded-xl bg-[#1FA7A2]/5 border-r-4 rtl:border-r-4 ltr:border-l-4 border-[#1FA7A2]">
                            <span class="text-xs text-slate-500 block mb-1">{{ __('beneficiary_name') }}:</span>
                            <span class="text-slate-900 font-bold text-sm">{{ $bank->account_name }}</span>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-100 text-slate-300 mb-4">
                            <i class="fas fa-university fa-2x"></i>
                        </div>
                        <p class="text-slate-500 font-medium">{{ __('no_bank_accounts') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
