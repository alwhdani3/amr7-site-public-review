<section class="w-full bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl border border-slate-100 font-['Tajawal'] relative overflow-hidden">
    
    {{-- Background Decoration --}}
    <div class="absolute top-0 right-0 w-32 h-32 bg-[#1FA7A2]/5 rounded-bl-[4rem] -mr-8 -mt-8 pointer-events-none"></div>

    {{-- Header --}}
    <div class="relative z-10 mb-8">
        <h2 class="text-2xl font-black text-slate-900 mb-3 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-[#1FA7A2]/10 flex items-center justify-center text-[#1FA7A2] shadow-sm">
                <i class="fas fa-shield-alt text-lg"></i>
            </span>
            {{ __('Two Factor Authentication') }}
        </h2>
        <p class="text-slate-500 text-sm leading-relaxed max-w-2xl">
            {{ __('Manage your two-factor authentication settings') }}
        </p>
    </div>

    <div class="flex flex-col w-full mx-auto space-y-6 text-sm relative z-10" wire:cloak>
        @if ($twoFactorEnabled)
            <div class="space-y-6 animate-fade-in"> {{-- إضافة أنيميشن بسيط --}}
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                        <i class="fas fa-check-circle me-1"></i> {{ __('Enabled') }}
                    </span>
                </div>

                <p class="text-slate-600 font-medium leading-relaxed">
                    {{ __('With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                </p>

                {{-- مكان أكواد الاسترداد --}}
                <livewire:settings.two-factor.recovery-codes />

                <div class="flex justify-start pt-2">
                    <button wire:click="disable" 
                            wire:loading.attr="disabled" {{-- تعطيل الزر أثناء التحميل --}}
                            class="px-6 py-3 rounded-xl border-2 border-red-100 text-red-600 font-bold hover:bg-red-50 hover:border-red-200 transition-all flex items-center gap-2 disabled:opacity-50">
                        
                        {{-- أيقونة التحميل --}}
                        <i wire:loading.remove wire:target="disable" class="fas fa-shield-alt"></i>
                        <i wire:loading wire:target="disable" class="fas fa-spinner fa-spin"></i>
                        
                        {{ __('Disable 2FA') }}
                    </button>
                </div>
            </div>
        @else
            <div class="space-y-6 animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-100">
                        <i class="fas fa-times-circle me-1"></i> {{ __('Disabled') }}
                    </span>
                </div>

                <p class="text-slate-600 font-medium leading-relaxed">
                    {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                </p>

                <button wire:click="enable" 
                        wire:loading.attr="disabled"
                        class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#1FA7A2] to-[#167F7B] text-white font-bold shadow-lg hover:shadow-xl hover:shadow-[#1FA7A2]/20 hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-2 disabled:opacity-70">
                    
                    {{-- أيقونة التحميل --}}
                    <i wire:loading.remove wire:target="enable" class="fas fa-shield-check"></i>
                    <i wire:loading wire:target="enable" class="fas fa-spinner fa-spin"></i>
                    
                    {{ __('Enable 2FA') }}
                </button>
            </div>
        @endif
    </div>

    {{-- 2FA Setup Modal --}}
    <x-modal name="two-factor-setup-modal" :show="$showModal" focusable>
        <div class="p-8 bg-white font-['Tajawal'] text-center">
            
            {{-- Modal Header --}}
            <div class="flex flex-col items-center space-y-6 mb-8">
                <div class="p-4 rounded-[2rem] bg-slate-50 border border-slate-100 shadow-inner">
                    <div class="p-4 rounded-2xl bg-white shadow-sm border border-slate-200 text-[#1FA7A2]">
                        <i class="fas fa-qrcode text-4xl"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-900">{{ $this->modalConfig['title'] }}</h3>
                    <p class="text-slate-500 text-sm font-medium leading-relaxed max-w-sm mx-auto">
                        {{ $this->modalConfig['description'] }}
                    </p>
                </div>
            </div>

            @if ($showVerificationStep)
                {{-- STEP 2: VERIFICATION --}}
                <div class="space-y-8 animate__animated animate__fadeIn">
                    <div class="flex flex-col items-center space-y-4">
                        {{-- تأكد أن مكون x-input-otp يدعم wire:model.live --}}
                        <x-input-otp :digits="6" name="code" wire:model.live="code" autocomplete="one-time-code" class="gap-2" />
                        
                        @error('code')
                            <p class="text-red-500 text-xs font-bold animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button wire:click="resetVerification" 
                                class="flex-1 py-3 rounded-xl border-2 border-slate-100 text-slate-600 font-bold hover:bg-slate-50 hover:text-slate-800 transition-all">
                            {{ __('Back') }}
                        </button>

                        <button wire:click="confirmTwoFactor" 
                                wire:loading.attr="disabled"
                                class="flex-1 py-3 rounded-xl bg-[#1FA7A2] text-white font-bold shadow-lg hover:shadow-xl hover:bg-[#167F7B] transition-all disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
                                {{-- استخدام Alpine لتعطيل الزر إذا كان الكود قصير --}}
                                x-bind:disabled="$wire.code.length < 6">
                            
                            <span wire:loading.remove wire:target="confirmTwoFactor">{{ __('Confirm') }}</span>
                            <i wire:loading wire:target="confirmTwoFactor" class="fas fa-spinner fa-spin"></i>
                        </button>
                    </div>
                </div>
            @else
                {{-- STEP 1: QR CODE --}}
                <div class="space-y-6 animate__animated animate__fadeIn">
                    
                    <div class="flex justify-center">
                        <div class="relative p-4 bg-white border-2 border-slate-100 rounded-2xl shadow-sm">
                            @empty($qrCodeSvg)
                                <div class="w-48 h-48 flex items-center justify-center bg-slate-50 rounded-xl animate-pulse">
                                    <i class="fas fa-spinner fa-spin text-slate-300 text-2xl"></i>
                                </div>
                            @else
                                <div class="w-48 h-48 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                    {!! $qrCodeSvg !!}
                                </div>
                            @endempty
                        </div>
                    </div>

                    <button wire:click="showVerificationIfNecessary" 
                            class="w-full py-3 rounded-xl bg-[#1FA7A2] text-white font-bold shadow-lg hover:shadow-xl hover:bg-[#167F7B] transition-all">
                        {{ $this->modalConfig['buttonText'] }}
                    </button>

                    <div class="pt-4 border-t border-slate-100">
                        <div class="relative flex items-center justify-center mb-4">
                            <span class="bg-white px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                {{ __('or, enter the code manually') }}
                            </span>
                        </div>

                        {{-- Manual Entry Copy (محسن) --}}
                        <div x-data="{
                                copied: false,
                                async copy() {
                                    if(!'{{ $manualSetupKey }}') return;
                                    try {
                                        await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                        this.copied = true;
                                        setTimeout(() => this.copied = false, 1500);
                                    } catch (e) { console.warn('Copy failed'); }
                                }
                            }" 
                            class="relative group cursor-pointer"
                            @click="copy()">
                            
                            <div class="flex items-center justify-between w-full p-4 bg-slate-50 border border-slate-200 rounded-xl hover:border-[#1FA7A2] hover:bg-[#1FA7A2]/5 transition-all group">
                                @empty($manualSetupKey)
                                    <div class="w-full flex justify-center"><i class="fas fa-spinner fa-spin text-slate-400"></i></div>
                                @else
                                    <span class="font-mono text-slate-600 font-bold tracking-wider select-all break-all text-xs md:text-sm">{{ $manualSetupKey }}</span>
                                    <span class="text-slate-400 group-hover:text-[#1FA7A2] transition-colors ml-2">
                                        <i class="fas" :class="copied ? 'fa-check text-emerald-500' : 'fa-copy'"></i>
                                    </span>
                                @endempty
                            </div>
                            
                            <div x-show="copied" x-transition class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-xs py-1 px-2 rounded">
                                {{ __('Copied!') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-modal>
</section>